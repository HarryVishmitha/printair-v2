<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoices\InvoicePdfService;
use Illuminate\Http\Request;

class InvoicePublicController extends Controller
{
    public function __construct(private readonly InvoicePdfService $pdf) {}

    private function assertTokenValid(Invoice $invoice, string $token): void
    {
        abort_unless($invoice->public_token_hash, 404);

        $hash = hash('sha256', $token);
        abort_unless(hash_equals((string) $invoice->public_token_hash, $hash), 403);

        abort_unless(
            $invoice->public_token_expires_at && now()->lessThanOrEqualTo($invoice->public_token_expires_at),
            403
        );
    }

    public function show(Request $request, Invoice $invoice, string $token)
    {
        $this->assertTokenValid($invoice, $token);

        $invoice->load([
            'order',
            'items.product',
            'items.roll',
            'items.variantSetItem.option',
            'items.finishings.finishingProduct',
            'items.finishings.option',
            'payments',
        ]);

        return view('public.invoices.show', [
            'invoice' => $invoice,
            'order' => $invoice->order,
            'token' => $token,
        ]);
    }

    public function download(Request $request, Invoice $invoice, string $token)
    {
        $this->assertTokenValid($invoice, $token);

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
}

