<?php

namespace App\Services\Estimates;

use App\Models\Estimate;
use Dompdf\Dompdf;
use Dompdf\Options;

class EstimatePdfService
{
    /**
     * @return string PDF binary bytes
     */
    public function render(Estimate $estimate, string $publicUrl): string
    {
        $estimate->loadMissing([
            'workingGroup',
            'customer',
            'items.product',
            'items.variantSetItem.option.group',
            'items.roll',
            'items.finishings',
        ]);

        $html = view('pdf.estimate', [
            'estimate' => $estimate,
            'publicUrl' => $publicUrl,
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
