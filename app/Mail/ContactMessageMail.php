<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactMessageMail extends Mailable
{
    public function __construct(
        public array $payload
    ) {}

    public function build()
    {
        return $this->subject('[Printair Contact] '.($this->payload['subject'] ?? 'New message'))
            ->markdown('emails.contact.message', [
                'p' => $this->payload,
            ]);
    }
}

