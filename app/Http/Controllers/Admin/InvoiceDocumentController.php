<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceDeliveryService;
use App\Services\Invoices\InvoicePdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceDocumentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly InvoicePdfService $pdf,
        private readonly InvoiceDeliveryService $delivery,
    ) {}

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdfBytes = $this->pdf->render($invoice);

        $no = $invoice->invoice_no ?: ('INV-'.$invoice->id);
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $no) ?: 'invoice';
        $filename = $safe.'.pdf';

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function email(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        if ($invoice->status === 'draft') {
            abort(422, 'Issue the invoice before emailing it.');
        }

        if (in_array((string) $invoice->status, ['void', 'refunded'], true)) {
            abort(422, 'Cannot email a void/refunded invoice.');
        }

        $ok = $this->delivery->emailIssuedInvoiceToCustomer($invoice, Auth::id());
        abort_unless($ok, 422, 'Order has no customer email.');

        return back()->with('success', 'Invoice emailed to customer.');
    }
}
