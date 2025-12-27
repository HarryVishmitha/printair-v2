<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $otp) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Printair verification code')
            ->greeting('Hi!')
            ->line('Use this code to verify your email for checkout:')
            ->line($this->otp)
            ->line('This code expires in 7 minutes.');
    }
}

