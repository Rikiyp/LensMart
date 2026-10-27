<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PriceDropped extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
        public int $oldPrice,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Harga turun!',
            'message' => "{$this->product->name} turun dari ".format_rupiah($this->oldPrice).' menjadi '.format_rupiah($this->product->price).'.',
            'url' => route('products.show', $this->product),
            'icon' => 'tag',
        ];
    }
}
