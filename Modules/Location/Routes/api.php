<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Location\Http\Controllers\LocationController;

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

// Route::prefix('location')->group(function() {
//     Route::get('/', 'LocationController@index')->name('test');
// });

Route::prefix('v1')
    ->as('v1.')
    ->middleware(['middleware' => 'api'])
    ->controller(LocationController::class)
    ->group(function () {
        Route::prefix('location')->group(function () {
                Route::get('/', 'index')->name('test');
                Route::post('/save', 'save')->name('test');

            });
});