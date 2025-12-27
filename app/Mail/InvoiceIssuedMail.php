<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly string $invoiceUrl,
        public readonly string $pdfUrl,
        public readonly string $pdfBinary,
    ) {}

    public function build()
    {
        $no = $this->invoice->invoice_no ?: ('INV-'.$this->invoice->id);

        return $this->subject("Invoice Issued: {$no} · Printair")
            ->view('emails.invoice-issued')
            ->with([
                'invoice' => $this->invoice,
                'invoiceUrl' => $this->invoiceUrl,
                'pdfUrl' => $this->pdfUrl,
            ])
            ->attachData($this->pdfBinary, $no.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
