<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $order->customer_id === $user->id;
        }

        return $user->isPartner()
            && $user->store
            && $order->store_id === $user->store->id
            && $order->isPaid();
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isPartner()
            && $user->store
            && $order->store_id === $user->store->id
            && $order->isPaid();
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->canBeCancelledBy($user);
    }

    public function confirmReceipt(User $user, Order $order): bool
    {
        return $user->isCustomer()
            && $order->customer_id === $user->id
            && $order->status === OrderStatus::Shipped;
    }
}
