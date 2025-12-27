<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllocatePaymentRequest;
use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\CreateInvoiceFromOrderRequest;
use App\Http\Requests\IssueInvoiceRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\VoidInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Invoices\InvoiceFlowService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly InvoiceFlowService $invoiceFlow,
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

        return redirect()
            ->route('admin.invoices.show', $invoice)
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
            'method' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'paid_at' => ['nullable', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'method' => $data['method'],
            'amount' => number_format((float) $data['amount'], 2, '.', ''),
            'currency' => $data['currency'] ?? ($invoice->currency ?? 'LKR'),
            'paid_at' => $data['paid_at'] ?? now(),
            'reference' => $data['reference'] ?? null,
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()?->id,
            'meta' => [
                'source' => 'manual_invoice_payment',
            ],
        ]);

        $this->paymentService->syncInvoiceAmounts($invoice);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice payment entry added.');
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
