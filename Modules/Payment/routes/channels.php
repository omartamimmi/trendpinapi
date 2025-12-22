<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports for the Payment module.
|
*/

/**
 * Retailer QR Sessions Channel
 *
 * Authorizes retailers to listen for updates on their QR payment sessions.
 */
Broadcast::channel('retailer.{retailerId}.qr-sessions', function ($user, $retailerId) {
    // Check if user is associated with the retailer
    return $user->retailer?->id === (int) $retailerId ||
           $user->retailers?->contains('id', (int) $retailerId);
});

/**
 * Customer Payments Channel
 *
 * Authorizes customers to listen for their payment updates.
 */
Broadcast::channel('customer.{userId}.payments', function ($user, $userId) {
    return $user->id === (int) $userId;
});

/**
 * Admin Payment Channel
 *
 * Authorizes admins to listen for all payment events (for monitoring).
 */
Broadcast::channel('admin.payments', function ($user) {
    return $user->hasRole('admin') || $user->hasRole('super_admin');
});
