<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

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
    ->group(function () {
        Route::prefix('file')
            ->as('file.')
            ->controller(MediaController::class)
            ->group(function () {
                Route::middleware(['auth:sanctum'])->group(function () {
                    // Upload endpoints
                    Route::post('store', 'store')->name('store');
                    Route::post('upload-multiple', 'uploadMultiple')->name('uploadMultiple');

                    // Retrieve endpoints
                    Route::get('get-all-media', 'allMedia')->name('allMedia');
                    Route::get('{id}', 'show')->name('show');
                    Route::post('get-by-ids', 'getByIds')->name('getByIds');

                    // Delete endpoint
                    Route::post('delete', 'delete')->name('delete');
                });
            });
    });
