<?php

namespace App\Observers;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Wishlist;
use App\Notifications\PriceDropped;

class ProductObserver
{
    public function saving(Product $product): void
    {
        if ($product->stock <= 0 && $product->status !== ProductStatus::Inactive) {
            $product->status = ProductStatus::OutOfStock;
        } elseif ($product->status === ProductStatus::OutOfStock && $product->stock > 0) {
            $product->status = ProductStatus::Active;
        }
    }

    public function updated(Product $product): void
    {
        if (! $product->wasChanged('price')) {
            return;
        }

        $oldPrice = (int) $product->getOriginal('price');

        if ($product->price >= $oldPrice) {
            return;
        }

        Wishlist::query()
            ->where('product_id', $product->id)
            ->with('user')
            ->chunkById(100, function ($wishlists) use ($product, $oldPrice) {
                foreach ($wishlists as $wishlist) {
                    if ($wishlist->user?->isCustomer() && $wishlist->user->is_active) {
                        $wishlist->user->notify(new PriceDropped($product, $oldPrice));
                        $wishlist->update(['price_when_added' => $product->price]);
                    }
                }
            });
    }
}
