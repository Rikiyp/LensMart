<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(User $user, Product $product, int $quantity = 1): CartItem
    {
        if (! $product->isPubliclyVisible() || $product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages(['quantity' => 'Produk ini sedang tidak tersedia.']);
        }

        $item = CartItem::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $newQuantity = ($item->quantity ?? 0) + max(1, $quantity);

        if ($newQuantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok hanya tersedia {$product->stock} unit.",
            ]);
        }

        $item->quantity = $newQuantity;
        $item->save();

        return $item;
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        $product = $item->product;

        if (! $product || $quantity < 1 || $quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah melebihi stok yang tersedia.',
            ]);
        }

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }
}
