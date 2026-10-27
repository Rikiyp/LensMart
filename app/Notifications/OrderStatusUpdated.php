<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $note = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = $notifiable->isPartner()
            ? route('partner.orders.show', $this->order)
            : route('customer.orders.show', $this->order);

        return [
            'title' => 'Status pesanan diperbarui',
            'message' => "Pesanan {$this->order->order_number} kini berstatus {$this->order->status->label()}.",
            'note' => $this->note,
            'url' => $url,
            'icon' => 'truck',
        ];
    }
}
