<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\AdminControllers\AdminShopController;

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

Route::as('admin.')->group(function () {

    Route::prefix('shop')->as('shop.')->middleware('auth')->controller(AdminShopController::class)->group(function () {
        Route::get('/', 'index')->name('shop-list')->can('admin_dashboard');
        Route::get('create', 'create')->name('shop-create')->can('create');
        Route::post('store', 'store')->name('shop-store');
        Route::get('show/{id}', 'show')->name('shop-show');
        Route::get('edit/{id}', 'edit')->name('shop-edit')->can('create');
        Route::post('update/{id}', 'update')->name('shop-update')->can('update');
        Route::post('destroy/{id}', 'destroy')->name('shop-destroy')->can('delete');

    });
    });
