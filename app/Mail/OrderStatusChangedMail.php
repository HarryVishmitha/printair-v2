<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly ?string $reason = null,
    ) {}

    public function build()
    {
        $no = $this->order->order_no ?: ('ORD-'.$this->order->id);
        $to = ucwords(str_replace('_', ' ', $this->toStatus));

        return $this->subject("Order {$no} status updated: {$to} · Printair")
            ->view('emails.orders.status-changed')
            ->with([
                'order' => $this->order,
                'fromStatus' => $this->fromStatus,
                'toStatus' => $this->toStatus,
                'reason' => $this->reason,
            ]);
    }
}

