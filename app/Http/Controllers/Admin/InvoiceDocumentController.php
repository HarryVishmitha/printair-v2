<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceIssuedMail;
use App\Models\Invoice;
use App\Services\Invoices\InvoicePdfService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvoiceDocumentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly InvoicePdfService $pdf) {}

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

        $invoice->loadMissing([
            'order',
            'items.finishings',
            'payments',
        ]);

        $order = $invoice->order;
        abort_unless($order, 422, 'Invoice has no order.');

        $to = $order->customer_email
            ?? data_get($order, 'email')
            ?? data_get($order->customer_snapshot, 'email')
            ?? null;

        abort_unless($to, 422, 'Order has no customer email.');

        // Rotate secure token for the public invoice page (short-lived).
        $token = Str::random(48);

        $invoice->update([
            'public_token_hash' => hash('sha256', $token),
            'public_token_expires_at' => now()->addMinutes(7),
            'updated_by' => Auth::id(),
        ]);

        $invoiceUrl = route('invoices.public.show', ['invoice' => $invoice->id, 'token' => $token]);
        $pdfUrl = route('invoices.public.download', ['invoice' => $invoice->id, 'token' => $token]);

        // Build PDF
        $pdfBytes = $this->pdf->render($invoice);

        Mail::to($to)->send(new InvoiceIssuedMail(
            invoice: $invoice,
            invoiceUrl: $invoiceUrl,
            pdfUrl: $pdfUrl,
            pdfBinary: $pdfBytes
        ));

        // Audit / meta
        $invoice->update([
            'updated_by' => Auth::id(),
            'meta' => array_merge($invoice->meta ?? [], [
                'last_emailed_to' => $to,
                'last_emailed_at' => now()->toISOString(),
                'emailed_by' => Auth::id(),
            ]),
        ]);

        return back()->with('success', 'Invoice emailed to customer.');
    }
}
