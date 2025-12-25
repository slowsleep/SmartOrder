<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Order;

Broadcast::channel('cooks.pending-items', function (User $user) {
    return $user->hasRole('cook');
});

Broadcast::channel('user.{id}.preparing-items', function (User $user, $id) {
    return (int) $user->id === (int) $id && $user->hasRole('cook');
});

Broadcast::channel('waiters.ready-items', function (User $user) {
    return $user->hasRole('waiter');
});

Broadcast::channel('user.{id}.delivery-items', function (User $user, $id) {
    return (int) $user->id === (int) $id && $user->hasRole('waiter');
});

Broadcast::channel('order.{orderId}', function (User $user, $orderId) {
    if ($user) {
        return $user->hasRole('admin') || $user->hasRole('cook') || $user->hasRole('waiter');
    }

    $sessionGuestToken = session()->get("order_{$orderId}_token");
    $order = Order::where('id', $orderId)->where('guest_token', $sessionGuestToken)->first();

    if (!$order) {
        return false;
    }

    return true;
});
