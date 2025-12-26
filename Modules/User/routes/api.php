<?php

use Illuminate\Support\Facades\Route;
use Modules\User\app\Http\Controllers\AuthController;
use Modules\User\app\Http\Controllers\InterestController;
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
        Route::post('login-with-phone', 'loginWithPhone')->name('loginWithPhone');
        Route::post('register', 'register')->name('register');

        // Two-step registration
        Route::post('register/init', 'registerInit')->name('registerInit');
        Route::post('register/complete', 'registerComplete')->name('registerComplete');

        // Social login routes
        Route::get('/login/{provider}', 'redirectToProvider');
        Route::get('/login/{provider}/callback', 'handleProviderCallback')->name('socialLogin');
        Route::post('/socialLogin', 'socialLoginMobile');

        // Protected user routes
        Route::middleware(['auth:sanctum'])
            ->group(function () {
                Route::post('logout', 'logout')->name('logout');
                Route::patch('update-user-profile', 'updateUserProfile')
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
                Route::post('remove-from-wishlist', 'removeFromWishlist')
                    ->name('removeFromWishlist');
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

// Interest routes
Route::prefix('v1/interests')
    ->as('v1.interests.')
    ->middleware(['api'])
    ->controller(InterestController::class)
    ->group(function () {
        // Public route - get all interests
        Route::get('/', 'index')->name('index');

        // Protected routes - require authentication
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/user', 'getUserInterests')->name('user');
            Route::post('/set', 'setInterests')->name('set');
            Route::post('/add', 'addInterests')->name('add');
            Route::post('/remove', 'removeInterests')->name('remove');
        });
    });
