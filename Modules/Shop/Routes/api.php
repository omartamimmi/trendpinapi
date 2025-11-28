<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\Api\ShopController;

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
    ->middleware(['middleware' => 'api'])
    ->controller(ShopController::class)
    ->group(function () {
        Route::prefix('shop')->as('shop.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{id}', 'detail')->name('detail');
                Route::get('/search/filters', 'getShopByFilter')->name('getShopByFilter');
                Route::middleware(['auth:sanctum'])->group(function () {
                        Route::post('store', 'store')->name('shop-store');
                        Route::post('update/{id}', 'update')->name('shop-update');
                        Route::post('update-status/{id}', 'updateStatus')->name('updateStatus');
                        Route::post('delete/{id}', 'delete')->name('delete');

                });
        });
        Route::prefix('branch')->as('branch.')->group(function () {
                // Route::get('/', 'index')->name('index');
                // Route::get('/{id}', 'detail')->name('detail');
                // Route::get('/search/filters', 'getShopByFilter')->name('getShopByFilter');
                Route::middleware(['auth:sanctum'])->group(function () {
                        Route::post('store-branch', 'storeBranch')->name('branch-store');
                        Route::post('update-branch/{id}', 'updateBranch')->name('branch-update');
                        Route::get('main-branches', 'getMainBranches')->name('main-branches');

                        // Route::post('update-status/{id}', 'updateStatus')->name('updateStatus');
                        // Route::post('delete/{id}', 'delete')->name('delete');

                });
        });
        Route::prefix('shops')->as('shops.')->group(function () {
                Route::get('/has-discount', 'shopsHasDiscount')->name('shopsHasDiscount');

        });

});

Route::prefix('v1')
    ->as('v1.')
    ->middleware(['middleware' => 'api'])
    ->controller(ShopController::class)
    ->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
                Route::get('get-author-shops', 'getAuthorShops')->name('author-shops');
                Route::get('get-author-shop/{id}', 'getAuthorShop')->name('author-shop');
        });
        Route::get('/get-offers-based-location', 'getOffersBasedLocation')->name('getOffersBasedLocation');

});
