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
use App\Models\Order;
use App\Models\Payment;
use App\Services\Invoices\InvoiceFlowService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
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

    public function showInvoice(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'order',
            'items.product',
            'items.roll',
            'items.variantSetItem',
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
