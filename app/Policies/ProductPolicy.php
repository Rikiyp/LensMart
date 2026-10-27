<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isPartner()
            && $user->store
            && $product->store_id === $user->store->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isPartner()
            && $user->store
            && $product->store_id === $user->store->id;
    }
}
