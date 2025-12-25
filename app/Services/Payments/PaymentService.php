<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function record(array $data): Payment
    {
        $actor = $this->actor();

        if (Gate::has('create')) {
            Gate::authorize('create', Payment::class);
        }

        $wgId = (int) ($data['working_group_id'] ?? 0);
        if ($wgId <= 0) {
            throw ValidationException::withMessages(['working_group_id' => 'Working group is required.']);
        }

        $amount = (string) ($data['amount'] ?? '0');
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Payment amount must be greater than 0.']);
        }

        $payment = Payment::create([
            'uuid' => (string) Str::uuid(),
            'working_group_id' => $wgId,
            'customer_id' => $data['customer_id'] ?? null,
            'method' => $data['method'] ?? 'cash',
            'status' => $data['status'] ?? 'pending',
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'LKR',
            'reference_no' => $data['reference_no'] ?? null,
            'received_at' => $data['received_at'] ?? null,
            'received_by' => $data['received_by'] ?? $actor->id,
            'meta' => $data['meta'] ?? null,
        ]);

        $this->activity('payment.created', $actor, $payment, [
            'amount' => $amount,
            'method' => $payment->method,
        ]);

        return $payment->fresh();
    }

    public function confirm(Payment $payment, array $meta = []): Payment
    {
        $actor = $this->actor();

        if (Gate::has('confirm')) {
            Gate::authorize('confirm', $payment);
        }

        return DB::transaction(function () use ($payment, $actor, $meta) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'confirmed') {
                return $payment;
            }

            if (in_array($payment->status, ['void', 'refunded', 'failed'], true)) {
                throw ValidationException::withMessages(['status' => 'This payment cannot be confirmed.']);
            }

            $payment->update([
                'status' => 'confirmed',
                'received_at' => $payment->received_at ?? now(),
                'received_by' => $payment->received_by ?? $actor->id,
            ]);

            $this->activity('payment.confirmed', $actor, $payment, $meta);

            return $payment->fresh();
        });
    }

    public function allocate(Payment $payment, Invoice $invoice, string $amount, array $meta = []): PaymentAllocation
    {
        $actor = $this->actor();

        if (Gate::has('allocate')) {
            Gate::authorize('allocate', [$payment, $invoice]);
        }

        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Allocation amount must be greater than 0.']);
        }

        return DB::transaction(function () use ($payment, $invoice, $amount, $actor, $meta) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            // WG scope safety
            if ((int) $payment->working_group_id !== (int) $invoice->working_group_id) {
                throw ValidationException::withMessages(['working_group' => 'Payment and Invoice working groups do not match.']);
            }

            if ($payment->status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Only confirmed payments can be allocated.']);
            }

            if (! in_array($invoice->status, ['issued', 'partial', 'overdue', 'paid'], true)) {
                throw ValidationException::withMessages(['invoice' => 'Invoice must be issued before allocating payments.']);
            }

            // Prevent over-allocations
            $allocatedSoFar = (string) $payment->allocations()->sum('amount');
            $remainingPayment = bcsub((string) $payment->amount, $allocatedSoFar, 2);
            if (bccomp($amount, $remainingPayment, 2) === 1) {
                throw ValidationException::withMessages(['amount' => 'Allocation exceeds remaining payment amount.']);
            }

            $invoiceDue = (string) $invoice->amount_due;
            if (bccomp($amount, $invoiceDue, 2) === 1) {
                throw ValidationException::withMessages(['amount' => 'Allocation exceeds invoice amount due.']);
            }

            $alloc = PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'created_by' => $actor->id,
                'created_at' => now(),
            ]);

            // Sync invoice financials + status
            $this->syncInvoiceAmounts($invoice);
            // Sync order payment status
            $this->syncOrderPaymentStatus($invoice->order);

            $this->activity('payment.allocated', $actor, $payment, [
                'invoice_id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'amount' => $amount,
            ] + $meta);

            return $alloc->fresh();
        });
    }

    public function syncInvoiceAmounts(Invoice $invoice): Invoice
    {
        $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

        $paid = (string) $invoice->allocations()->sum('amount');
        $grand = (string) $invoice->grand_total;

        $due = bcsub($grand, $paid, 2);
        if (bccomp($due, '0', 2) === -1) {
            $due = '0.00';
        }

        // Update invoice status based on due
        $newStatus = $invoice->status;
        if (in_array($invoice->status, ['void', 'refunded'], true)) {
            // Keep terminal state
            $newStatus = $invoice->status;
        } else {
            if (bccomp($due, '0', 2) === 0) {
                $newStatus = 'paid';
            } elseif (bccomp($paid, '0', 2) === 1) {
                $newStatus = 'partial';
            } else {
                // keep issued/overdue depending on due date logic (optional)
                $newStatus = $invoice->status === 'overdue' ? 'overdue' : 'issued';
            }
        }

        $invoice->update([
            'amount_paid' => $paid,
            'amount_due' => $due,
            'status' => $newStatus,
            'paid_at' => $newStatus === 'paid' ? now() : null,
            'updated_by' => $this->actor()->id,
        ]);

        return $invoice->fresh();
    }

    public function syncOrderPaymentStatus(?Order $order): ?Order
    {
        if (! $order) {
            return null;
        }

        $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

        $invoices = $order->invoices()->whereNull('deleted_at')->get();
        if ($invoices->count() === 0) {
            $order->update(['payment_status' => 'unpaid']);
            return $order->fresh();
        }

        $anyPartial = $invoices->contains(fn ($inv) => in_array($inv->status, ['partial'], true));
        $allPaid = $invoices->every(fn ($inv) => $inv->status === 'paid');

        $status = 'unpaid';
        if ($allPaid) {
            $status = 'paid';
        } elseif ($anyPartial) {
            $status = 'partial';
        }

        $order->update([
            'payment_status' => $status,
            'updated_by' => $this->actor()->id,
        ]);

        return $order->fresh();
    }

    // ---------------- internals ----------------

    private function actor(): User
    {
        $u = Auth::user();
        if (! $u instanceof User) {
            throw ValidationException::withMessages(['auth' => 'Unauthorized.']);
        }
        return $u;
    }

    private function activity(string $action, User $actor, $subject, array $properties = []): void
    {
        // Wire into your activity_logs here later
    }
}

