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


Route::prefix('file')
    ->as('file.')
    ->controller(MediaController::class)
    ->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('store', 'store')
                ->name('store');

            Route::get('get-all-media', 'allMedia')
                ->name('allMedia');

            Route::post('delete', 'delete')
                ->name('delete');
        });
        // Route::get('private/view', 'privateFileView')
        //     ->name('media.private.view');
        // Route::get('get-image', 'getImage')
        //     ->name('getImage');
    });
