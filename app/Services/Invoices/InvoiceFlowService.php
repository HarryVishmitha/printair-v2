<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceItemFinishing;
use App\Models\InvoiceStatusHistory;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceFlowService
{
    public function createFromOrder(Order $order, string $type = 'final', array $meta = []): Invoice
    {
        $actor = $this->actor();

        if (Gate::has('createFromOrder')) {
            Gate::authorize('createFromOrder', [$order, $type]);
        }

        $validTypes = ['final', 'partial', 'credit_note'];
        if (! in_array($type, $validTypes, true)) {
            throw ValidationException::withMessages(['type' => 'Invalid invoice type.']);
        }

        return DB::transaction(function () use ($order, $type, $meta, $actor) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Basic rule: don’t invoice cancelled orders
            if (in_array($order->status, ['cancelled', 'refunded'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Cannot create an invoice for a cancelled/refunded order.',
                ]);
            }

            // One order -> one FINAL invoice
            if ($type === 'final') {
                $exists = Invoice::query()
                    ->where('order_id', $order->id)
                    ->where('type', 'final')
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'type' => 'This order already has a final invoice.',
                    ]);
                }
            }

            $invoiceNo = $this->generateInvoiceNo($order->working_group_id);

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'invoice_no' => $invoiceNo,

                'working_group_id' => $order->working_group_id,
                'order_id' => $order->id,

                'type' => $type,
                'status' => 'draft',

                'customer_snapshot' => $order->customer_snapshot,

                'currency' => $order->currency,

                'due_at' => now()->addDays(14),

                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'tax_total' => $order->tax_total,
                'shipping_fee' => $order->shipping_fee,
                'other_fee' => $order->other_fee,
                'grand_total' => $order->grand_total,

                'amount_paid' => 0,
                'amount_due' => $order->grand_total,

                'meta' => $meta ?: null,

                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $order->load(['items.finishings']);

            foreach ($order->items as $oi) {
                $ii = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $oi->id,
                    'working_group_id' => $invoice->working_group_id,

                    'product_id' => $oi->product_id,
                    'variant_set_item_id' => $oi->variant_set_item_id,
                    'roll_id' => $oi->roll_id,

                    'title' => $oi->title,
                    'description' => $oi->description,

                    'qty' => $oi->qty,

                    'width' => $oi->width,
                    'height' => $oi->height,
                    'unit' => $oi->unit,
                    'area_sqft' => $oi->area_sqft,
                    'offcut_sqft' => $oi->offcut_sqft,

                    'unit_price' => $oi->unit_price,
                    'line_subtotal' => $oi->line_subtotal,
                    'discount_amount' => $oi->discount_amount,
                    'tax_amount' => $oi->tax_amount,
                    'line_total' => $oi->line_total,

                    'pricing_snapshot' => $oi->pricing_snapshot,
                    'sort_order' => $oi->sort_order,
                ]);

                foreach ($oi->finishings as $of) {
                    InvoiceItemFinishing::create([
                        'invoice_item_id' => $ii->id,
                        'order_item_finishing_id' => $of->id,
                        'finishing_product_id' => $of->finishing_product_id,
                        'option_id' => $of->option_id,
                        'label' => $of->label,
                        'qty' => $of->qty,
                        'unit_price' => $of->unit_price,
                        'total' => $of->total,
                        'pricing_snapshot' => $of->pricing_snapshot,
                    ]);
                }
            }

            $this->logStatusChange($invoice, null, 'draft', $actor, 'Invoice draft created', [
                'invoice_no' => $invoiceNo,
                'order_no' => $order->order_no,
            ]);

            $this->activity('invoice.created', $actor, $invoice, [
                'invoice_no' => $invoiceNo,
                'order_id' => $order->id,
            ]);

            return $invoice->fresh(['items']);
        });
    }

    public function issue(Invoice $invoice, array $meta = []): Invoice
    {
        $actor = $this->actor();

        if (Gate::has('issue')) {
            Gate::authorize('issue', $invoice);
        }

        return DB::transaction(function () use ($invoice, $actor, $meta) {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only a draft invoice can be issued.',
                ]);
            }

            if ($invoice->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Invoice must contain items before issuing.',
                ]);
            }

            $from = $invoice->status;

            $updates = [
                'status' => 'issued',
                'issued_at' => now(),
                'locked_at' => now(),
                'locked_by' => $actor->id,
                'updated_by' => $actor->id,
            ];

            // Optional columns (safe across environments)
            if (Schema::hasColumn('invoices', 'pricing_frozen_at')) {
                $updates['pricing_frozen_at'] = $invoice->pricing_frozen_at ?? now();
            }

            if (Schema::hasColumn('invoices', 'pricing_snapshot')) {
                $updates['pricing_snapshot'] = $invoice->pricing_snapshot ?? [
                    'frozen_from' => 'invoice',
                    'frozen_at' => now()->toISOString(),
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'order_id' => $invoice->order_id,
                    'type' => $invoice->type,
                    'totals' => [
                        'subtotal' => (string) $invoice->subtotal,
                        'discount_total' => (string) $invoice->discount_total,
                        'tax_total' => (string) $invoice->tax_total,
                        'shipping_fee' => (string) $invoice->shipping_fee,
                        'other_fee' => (string) $invoice->other_fee,
                        'grand_total' => (string) $invoice->grand_total,
                    ],
                ];
            }

            $invoice->update($updates);

            // Freeze final order total when final invoice is issued.
            if ($invoice->type === 'final' && Schema::hasColumn('orders', 'final_grand_total')) {
                $order = Order::query()->whereKey($invoice->order_id)->lockForUpdate()->first();
                if ($order) {
                    $orderUpdates = [
                        'final_grand_total' => $invoice->grand_total,
                        'updated_by' => $actor->id,
                    ];

                    if (Schema::hasColumn('orders', 'locked_at')) {
                        $orderUpdates['locked_at'] = $order->locked_at ?? now();
                    }
                    if (Schema::hasColumn('orders', 'locked_by')) {
                        $orderUpdates['locked_by'] = $order->locked_by ?? $actor->id;
                    }

                    $order->update($orderUpdates);
                }
            }

            $this->logStatusChange($invoice, $from, 'issued', $actor, $meta['reason'] ?? 'Invoice issued', $meta);
            $this->activity('invoice.issued', $actor, $invoice, $meta);

            return $invoice->fresh();
        });
    }

    public function void(Invoice $invoice, string $reason, array $meta = []): Invoice
    {
        $actor = $this->actor();

        if (Gate::has('void')) {
            Gate::authorize('void', $invoice);
        }

        return DB::transaction(function () use ($invoice, $actor, $reason, $meta) {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (in_array($invoice->status, ['void', 'refunded'], true)) {
                return $invoice;
            }

            $from = $invoice->status;

            $invoice->update([
                'status' => 'void',
                'voided_at' => now(),
                'updated_by' => $actor->id,
            ]);

            $this->logStatusChange($invoice, $from, 'void', $actor, $reason, $meta);
            $this->activity('invoice.voided', $actor, $invoice, ['reason' => $reason] + $meta);

            return $invoice->fresh();
        });
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

    private function logStatusChange(Invoice $invoice, ?string $from, string $to, User $actor, ?string $reason = null, array $meta = []): void
    {
        InvoiceStatusHistory::create([
            'invoice_id' => $invoice->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $actor->id,
            'reason' => $reason,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    private function activity(string $action, User $actor, $subject, array $properties = []): void
    {
        // Wire into your activity_logs here later
    }

    private function generateInvoiceNo(int $wgId): string
    {
        $date = now()->format('Ymd');

        for ($i = 0; $i < 5; $i++) {
            $rand = str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $no = "INV-WG{$wgId}-{$date}-{$rand}";

            if (! Invoice::query()->where('working_group_id', $wgId)->where('invoice_no', $no)->exists()) {
                return $no;
            }
        }

        throw ValidationException::withMessages([
            'invoice_no' => 'Unable to generate a unique invoice number. Please try again.',
        ]);
    }
}
