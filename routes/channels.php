<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{id}', function ($user, $id) {
    $order = \App\Models\Order::find($id);
    if (!$order) return false;
    
    return (int) $user->id === (int) $order->customer_id || 
           ((int) $user->id === (int) $order->store?->user_id);
});

Broadcast::channel('customer.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('store.{id}', function ($user, $id) {
    return (int) $user->store?->id === (int) $id;
});
