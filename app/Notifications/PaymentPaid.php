<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentPaid extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public ?Order $order = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($notifiable->isPartner() && $this->order) {
            return [
                'title' => 'Pesanan baru sudah dibayar',
                'message' => "Pesanan {$this->order->order_number} sebesar ".format_rupiah($this->order->total).' siap diproses.',
                'url' => route('partner.orders.show', $this->order),
                'icon' => 'shopping-bag',
            ];
        }

        return [
            'title' => 'Pembayaran berhasil',
            'message' => "Pembayaran {$this->payment->code} sebesar ".format_rupiah($this->payment->amount).' telah kami terima.',
            'url' => route('customer.orders.index'),
            'icon' => 'check-circle',
        ];
    }
}
