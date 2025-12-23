<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        return $this->subject('Printair Contact: '.($this->payload['subject'] ?? 'New Message'))
            ->replyTo($this->payload['email'] ?? 'noreply@printair.lk', $this->payload['name'] ?? 'Customer')
            ->view('emails.contact-message')
            ->with([
                'payload' => $this->payload,
            ]);
    }
}
