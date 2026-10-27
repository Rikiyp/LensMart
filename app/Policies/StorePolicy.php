<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function update(User $user, Store $store): bool
    {
        return $user->isPartner() && $store->user_id === $user->id;
    }
}
