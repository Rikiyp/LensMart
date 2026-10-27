<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    /**
     * Admin bisa melihat semua payout. Partner hanya milik tokonya.
     */
    public function view(User $user, Payout $payout): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isPartner()
            && $user->store
            && $payout->store_id === $user->store->id;
    }

    /**
     * Hanya admin yang dapat menandai payout sebagai dibayar.
     */
    public function markPaid(User $user, Payout $payout): bool
    {
        return $user->isAdmin();
    }
}
