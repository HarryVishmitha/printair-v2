<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllocatePaymentRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\CreateInvoiceFromOrderRequest;
use App\Http\Requests\IssueInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Requests\VoidInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Invoices\InvoiceFlowService;
use App\Services\Invoices\InvoiceEditService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly InvoiceFlowService $invoiceFlow,
        private readonly InvoiceEditService $invoiceEdit,
        private readonly PaymentService $paymentService,
    ) {}

    public function createInvoiceFromOrder(CreateInvoiceFromOrderRequest $request, Order $order)
    {
        $data = $request->validated();

        $invoice = $this->invoiceFlow->createFromOrder(
            $order,
            $data['type'] ?? 'final',
            ['reason' => $data['reason'] ?? null]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $invoice->id,
                'redirect_url' => route('admin.invoices.show', $invoice),
                'edit_url' => route('admin.invoices.edit', $invoice),
            ]);
        }

        return redirect()
            ->route('admin.invoices.edit', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function invoicesIndex(Request $request)
    {
        $q = Invoice::query()
            ->with(['order', 'createdBy'])
            ->latest('id');

        if ($wgId = $request->integer('working_group_id')) {
            $q->where('working_group_id', $wgId);
        }

        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }

        if ($type = $request->string('type')->toString()) {
            $q->where('type', $type);
        }

        if ($search = trim((string) $request->get('search'))) {
            $q->where(function ($sub) use ($search) {
                $sub->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_no', 'like', "%{$search}%"));
            });
        }

        $invoices = $q->paginate(20)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'order',
            'items.product',
            'items.finishings.finishingProduct',
            'items.finishings.option',
            'items.roll',
            'items.variantSetItem',
            'payments.createdBy',
            'statusHistories.changedBy',
            'allocations.payment.receivedBy',
            'createdBy',
            'updatedBy',
        ]);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function editInvoice(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load([
            'order',
            'items.product',
            'items.finishings.finishingProduct',
            'items.finishings.option',
            'items.roll',
            'items.variantSetItem.option',
        ]);

        return view('admin.invoices.form', [
            'mode' => 'edit',
            'invoice' => $invoice,
        ]);
    }

    public function updateInvoice(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        try {
            $actor = $request->user();
            if (! $actor instanceof \App\Models\User) {
                abort(401);
            }

            $this->invoiceEdit->updateDraft($invoice, $request->validated(), $actor);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $invoice->id,
                'redirect_url' => route('admin.invoices.show', $invoice),
                'edit_url' => route('admin.invoices.edit', $invoice),
            ]);
        }

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice updated.');
    }

    public function issueInvoice(IssueInvoiceRequest $request, Invoice $invoice)
    {
        $this->invoiceFlow->issue($invoice, $request->validated());

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice issued and locked.');
    }

    public function addInvoicePayment(Request $request, Invoice $invoice)
    {
        $this->authorize('addPayment', $invoice);

        if ($invoice->status === 'draft') {
            abort(422, 'Issue the invoice before adding payments.');
        }

        if (in_array((string) $invoice->status, ['void', 'refunded'], true)) {
            abort(422, 'Cannot add payments to a void/refunded invoice.');
        }

        $data = $request->validate([
            'method' => ['required', 'string', 'in:cash,bank,card,online,adjustment'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = number_format((float) $data['amount'], 2, '.', '');

        // Adjustments (negative ledger entries) stay invoice-only.
        if ($data['method'] === 'adjustment' || bccomp($amount, '0', 2) === -1) {
            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'method' => $data['method'],
                'amount' => $amount,
                'currency' => $data['currency'] ?? ($invoice->currency ?? 'LKR'),
                'paid_at' => $data['paid_at'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $request->user()?->id,
                'meta' => [
                    'source' => 'manual_invoice_adjustment',
                ],
            ]);

            $this->paymentService->syncInvoiceAmounts($invoice);
            $this->paymentService->syncOrderPaymentStatus($invoice->order);

            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice entry added.');
        }

        // For real payments, create a Payment record and allocate it in one step.
        DB::transaction(function () use ($invoice, $data, $amount, $request) {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $order = $invoice->order()->lockForUpdate()->first();

            $payMethod = match ($data['method']) {
                'cash' => 'cash',
                'card' => 'card',
                'bank' => 'bank_transfer',
                'online' => 'online_gateway',
                default => 'cash',
            };

            $payment = $this->paymentService->record([
                'working_group_id' => $invoice->working_group_id,
                'customer_id' => $order?->customer_id,
                'method' => $payMethod,
                'status' => 'confirmed',
                'amount' => $amount,
                'currency' => $data['currency'] ?? ($invoice->currency ?? 'LKR'),
                'reference_no' => $data['reference'] ?? null,
                'received_at' => $data['paid_at'] ?? now(),
                'received_by' => $request->user()?->id,
                'meta' => [
                    'source' => 'invoice_payment_form',
                    'note' => $data['note'] ?? null,
                    'invoice_id' => $invoice->id,
                ],
            ]);

            $this->paymentService->allocate($payment, $invoice, $amount, [
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment recorded.');
    }

    public function markInvoicePaid(Request $request, Invoice $invoice)
    {
        $this->authorize('addPayment', $invoice);

        $data = $request->validate([
            'method' => ['required', 'string', 'in:cash,bank,card,online'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($invoice, $data, $request) {
                $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

                if ($invoice->status === 'draft') {
                    throw ValidationException::withMessages([
                        'status' => 'Issue the invoice before recording payments.',
                    ]);
                }

                if (in_array((string) $invoice->status, ['void', 'refunded'], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Cannot add payments to a void/refunded invoice.',
                    ]);
                }

                $invoice = $this->paymentService->syncInvoiceAmounts($invoice);
                $due = (string) $invoice->amount_due;

                if (bccomp($due, '0', 2) <= 0) {
                    throw ValidationException::withMessages([
                        'amount' => 'Invoice has no balance due.',
                    ]);
                }

                $order = $invoice->order()->lockForUpdate()->first();

                $payMethod = match ($data['method']) {
                    'cash' => 'cash',
                    'card' => 'card',
                    'bank' => 'bank_transfer',
                    'online' => 'online_gateway',
                    default => 'cash',
                };

                $payment = $this->paymentService->record([
                    'working_group_id' => $invoice->working_group_id,
                    'customer_id' => $order?->customer_id,
                    'method' => $payMethod,
                    'status' => 'confirmed',
                    'amount' => $due,
                    'currency' => $invoice->currency ?? 'LKR',
                    'reference_no' => $data['reference'] ?? null,
                    'received_at' => now(),
                    'received_by' => $request->user()?->id,
                    'meta' => [
                        'source' => 'invoice_quick_mark_paid',
                        'note' => $data['note'] ?? 'Marked paid from invoice screen',
                        'invoice_id' => $invoice->id,
                    ],
                ]);

                $this->paymentService->allocate($payment, $invoice, $due, [
                    'note' => $data['note'] ?? 'Marked paid from invoice screen',
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded as paid in full.');
    }

    public function voidInvoice(VoidInvoiceRequest $request, Invoice $invoice)
    {
        $data = $request->validated();
        $this->invoiceFlow->void($invoice, $data['reason']);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice voided.');
    }

    public function recordPayment(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->record($request->validated());

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment recorded.');
    }

    public function paymentsIndex(Request $request)
    {
        $q = Payment::query()
            ->with(['customer', 'workingGroup', 'receivedBy'])
            ->latest('id');

        if ($wgId = $request->integer('working_group_id')) {
            $q->where('working_group_id', $wgId);
        }

        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }

        if ($method = $request->string('method')->toString()) {
            $q->where('method', $method);
        }

        if ($search = trim((string) $request->get('search'))) {
            $q->where(function ($sub) use ($search) {
                $sub->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        $payments = $q->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function showPayment(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load([
            'customer',
            'workingGroup',
            'receivedBy',
            'allocations.invoice.order',
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    public function confirmPayment(ConfirmPaymentRequest $request, Payment $payment)
    {
        $this->paymentService->confirm($payment, $request->validated());

        return redirect()
            ->route('admin.payments.show', $payment)
            ->with('success', 'Payment confirmed.');
    }

    public function allocatePayment(AllocatePaymentRequest $request, Payment $payment, Invoice $invoice)
    {
        $data = $request->validated();

        $this->paymentService->allocate($payment, $invoice, number_format((float) $data['amount'], 2, '.', ''), [
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment allocated to invoice.');
    }
}
