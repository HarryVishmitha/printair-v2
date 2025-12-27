<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'order_submitted',
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'working_group_id' => $this->order->working_group_id,
            'message' => "New order submitted: {$this->order->order_no}",
            'url' => route('admin.orders.show', $this->order->id),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Order Submitted — {$this->order->order_no}")
            ->greeting("Hi!")
            ->line("A new order has been submitted.")
            ->line("Order No: {$this->order->order_no}")
            ->action('Review Order', route('admin.orders.show', $this->order->id));
    }
}

