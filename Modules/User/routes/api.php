<?php

use Illuminate\Support\Facades\Route;
use Modules\User\app\Http\Controllers\AuthController;
use Modules\User\app\Http\Controllers\WishlistController;
use Modules\User\app\Http\Controllers\NotificationController;

// Retailer onboarding routes are handled by RetailerOnboarding module

// User module API routes - Single source of truth for authentication
Route::prefix('v1')
    ->as('v1.')
    ->middleware(['api'])
    ->controller(AuthController::class)
    ->group(function () {
        // Public authentication routes
        Route::post('login', 'login')->name('login');
        Route::post('register', 'register')->name('register');

        // Social login routes
        Route::get('/login/{provider}', 'redirectToProvider');
        Route::get('/login/{provider}/callback', 'handleProviderCallback')->name('socialLogin');
        Route::post('/socialLogin', 'socialLoginMobile');

        // Protected user routes
        Route::middleware(['auth:sanctum'])
            ->group(function () {
                Route::post('logout', 'logout')->name('logout');
                Route::match(['post', 'put'], 'update-user-profile', 'updateUserProfile')
                    ->name('updateUserProfile');
                Route::get('get-user-profile', 'getUserProfile')
                    ->name('getUserProfile');
                Route::post('change-password', 'changeMyPassword')
                    ->name('changePassword');
                Route::post('destroy', 'destroy')
                    ->name('user-delete');
                Route::post('/save-token', 'saveToken')
                    ->name('saveToken');
                Route::post('/send-notification', 'sendNotification')
                    ->name('sendNotification');
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
