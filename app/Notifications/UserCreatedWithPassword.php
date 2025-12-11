<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedWithPassword extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The plain-text password to send to the user.
     */
    protected string $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'PrintAir');

        return (new MailMessage)
            ->subject("Welcome to {$appName} – Your Account Details")
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("An account has been created for you on {$appName}.")
            ->line('Here are your login credentials:')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Password:** {$this->password}")
            ->action('Login Now', url('/login'))
            ->line('For security, we recommend changing your password after your first login.')
            ->salutation("Best regards,\nThe {$appName} Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your account has been created.',
        ];
    }
}
