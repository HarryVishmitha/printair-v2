<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your email for Printair')
            ->greeting('Hi '.($notifiable->first_name ?? 'there').' 👋')
            ->line('Welcome to Printair Advertising! To activate your account and access your quotations, orders, and design files, please confirm your email address.')
            ->action('Verify my email', $verificationUrl)
            ->line('If you didn’t create a Printair account, you can safely ignore this email.')
            ->salutation("Warm regards,\nPrintair Advertising")
            ->markdown('emails.auth.verify-email', [
                'url' => $verificationUrl,
                'user' => $notifiable,
            ]);
    }
}
