<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('cooks.pending-items', function (User $user) {
    return $user->hasRole('cook');
});

Broadcast::channel('user.{id}.preparing-items', function (User $user, $id) {
    return (int) $user->id === (int) $id && $user->hasRole('cook');
});
