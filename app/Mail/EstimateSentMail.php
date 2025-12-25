<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstimateSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Estimate $estimate,
        public readonly string $publicUrl,
        public readonly string $pdfBytes,
        public readonly string $pdfFilename,
        public readonly array $meta = [],
    ) {}

    public function build()
    {
        $subjectNo = $this->estimate->estimate_no ?: ('EST-'.$this->estimate->id);

        return $this->subject("Estimate {$subjectNo} · Printair")
            ->view('emails.estimates.sent')
            ->with([
                'estimate' => $this->estimate,
                'publicUrl' => $this->publicUrl,
                'meta' => $this->meta,
            ])
            ->attachData($this->pdfBytes, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ]);
    }
}

