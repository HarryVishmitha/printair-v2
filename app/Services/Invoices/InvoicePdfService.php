<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfService
{
    /**
     * @return string PDF binary bytes
     */
    public function render(Invoice $invoice): string
    {
        $invoice->loadMissing([
            'workingGroup',
            'order',
            'items.product',
            'items.roll',
            'items.variantSetItem.option',
            'items.finishings.finishingProduct',
            'items.finishings.option',
            'payments',
        ]);

        $html = view('pdf.invoice', [
            'invoice' => $invoice,
            'order' => $invoice->order,
        ])->render();

        $options = new Options();
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsPhpEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}

