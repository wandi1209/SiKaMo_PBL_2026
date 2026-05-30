<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('merchant.{merchantId}', function ($user, int $merchantId) {
    if ($user->role === 'owner') {
        return (int) $user->merchant?->id === $merchantId;
    }

    if ($user->role === 'cashier') {
        return (int) $user->merchant_id === $merchantId;
    }

    return false;
});
