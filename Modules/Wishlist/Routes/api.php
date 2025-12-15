<?php

use Illuminate\Support\Facades\Route;
use Modules\Wishlist\Http\Controllers\WishlistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')
    ->as('v1.')
    ->middleware(['api'])
    ->prefix('wishlist')
    ->as('wishlist.')
    ->controller(WishlistController::class)
    ->group(function () {
        // All wishlist routes require authentication
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/add', 'addToWishlist')->name('add');
            Route::delete('/remove/{shopId}', 'removeFromWishlist')->name('remove');
            Route::get('/check/{shopId}', 'checkIfInWishlist')->name('check');
            Route::delete('/clear', 'clearWishlist')->name('clear');
        });
    });