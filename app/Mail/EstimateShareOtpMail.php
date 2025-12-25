<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstimateShareOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Estimate $estimate,
        public readonly string $code,
        public readonly int $expiresMinutes,
    ) {}

    public function build()
    {
        $no = $this->estimate->estimate_no ?: ('EST-'.$this->estimate->id);

        return $this->subject("OTP verification for {$no} · Printair")
            ->view('emails.estimates.share-otp')
            ->with([
                'estimate' => $this->estimate,
                'code' => $this->code,
                'expiresMinutes' => $this->expiresMinutes,
            ]);
    }
}

