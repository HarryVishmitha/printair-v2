<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $message;
    public ?string $actionUrl;
    public string $actionText;
    public string $type; // 'info', 'success', 'warning', 'error'

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, ?string $actionUrl = null, string $actionText = 'View', string $type = 'info')
    {
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
                    ->subject('System Notification - Printair')
                    ->line($this->message);

        if ($this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'type' => $this->type,
        ];
    }
}
