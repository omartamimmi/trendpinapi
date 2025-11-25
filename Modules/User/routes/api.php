<?php

use Illuminate\Support\Facades\Route;
use Modules\User\app\Http\Controllers\RetailerController;
use Modules\User\app\Http\Controllers\AuthController;
use Modules\User\app\Http\Controllers\UserController;
use Modules\User\app\Http\Controllers\WishlistController;
use Modules\User\app\Http\Controllers\NotificationController;

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('users', UserController::class)->names('user');
// });

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('retailer')->as('retailer.')->group(function () {
        Route::prefix('step')->group(function () {
            Route::get('get', [RetailerController::class, 'stepGet'])->name('get');
            Route::post('create', [RetailerController::class, 'stepCreate'])->name('create');
            Route::patch('update', [RetailerController::class, 'stepUpdate'])->name('update');
        });
    });
});



Route::prefix('v1')
    ->as('v1.')
    ->middleware(['api'])
    ->controller(AuthController::class)
    ->group(function () {
        Route::post('register', 'register')
            ->name('register');
        Route::post('login', 'login')
            ->name('login');
        Route::get('/login/{provider}', 'redirectToProvider');
        Route::get('/login/{provider}/callback', 'handleProviderCallback')->name('socialLogin');

        Route::post('/socialLogin', 'socialLoginMobile');
        Route::get('/ss', [AuthController::class, 'handleGoogleCallback']);
        Route::post('/send-notification-based-location', 'sendNotificationBasedLocation')
        ->name('send.notification.basedLocation');

        Route::middleware(['auth:sanctum'])
            ->group(function () {
                    Route::post('logout', 'logout')
                        ->name('logout');
                    Route::put('update-user-profile', 'updateUserProfile')
                        ->name('updateUserProfile');
                    Route::get('get-user-profile', 'getUserProfile')
                        ->name('getUserProfile');
                    Route::put('change-password', 'changeMyPassword')
                        ->name('changePassword');      
                    Route::post('destroy','destroy')
                        ->name('user-delete');
                    Route::post('/save-token', 'saveToken')
                        ->name('saveToken');
                    Route::post('/send-notification', 'sendNotification')
                        ->name('sendNotification');
                 
                    Route::get('/get-notification-based-location', 'getNotificationTest')
                        ->name('get.notification.basedLocation');
                });

    });

    Route::prefix('v1')
    ->as('v1.')
    ->middleware(['api'])
    ->controller(WishlistController::class)
    ->group(function () {
        Route::middleware(['auth:sanctum'])
            ->group(function () {
                    Route::post('add-to-wishlist', 'addToWishlist')
                        ->name('addToWishlist');
                    Route::post('remove-from-wishlist', 'removeShopFromWishlist')
                        ->name('removeShopFromWishlist');
                    Route::get('get-user-wishlist', 'getAllUserWishlist')
                        ->name('getAllUserWishlist');
                });

    });

    Route::prefix('v1')
    ->as('v1.')
    ->middleware(['api'])
    ->controller(NotificationController::class)
    ->group(function () {
        Route::middleware(['auth:sanctum'])
            ->group(function () {
                    Route::get('set-user-interest-to-shop', 'userInterestToShop')
                        ->name('userInterestToShop');
                });

    });
