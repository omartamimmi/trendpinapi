<?php
use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\ShopController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::prefix('user')->as('user.')->group( function () {
//     Route::prefix('shop')->as('shop.')->middleware('auth')->controller(ShopController::class)->group(function() {
//         Route::get('index','index')->name('shop-list');
//         Route::get('create','create')->name('shop-create');
//         Route::post('store', 'store')->name('shop-store');
//         Route::get('edit/{id}', 'edit')->name('shop-edit');
//         Route::post('update/{id}', 'update')->name('shop-update');
//         Route::post('destroy/{id}', 'destroy')->name('shop-destroy');
//         Route::get('getForSelect2', 'getForSelect2')->name('getForSelect2');
//     });
// });

