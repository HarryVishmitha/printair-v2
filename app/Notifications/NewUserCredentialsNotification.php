<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The plain-text password.
     *
     * @var string
     */
    public $password;

    /**
     * Create a new notification instance.
     *
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $loginUrl = route('login');

        return (new MailMessage)
            ->subject('Your New Account Credentials')
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('An account has been created for you. Please use the following credentials to log in:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Temporary Password:** ' . $this->password)
            ->line('You will be required to change your password upon first login for security reasons.')
            ->action('Login Now', $loginUrl)
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Regards,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
