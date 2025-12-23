<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\app\Http\Controllers\CategoryController;

Route::prefix('v1')->group(function () {
    Route::prefix('category')
        ->controller(CategoryController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'detail')->name('detail');
    });
});
// Route::prefix('v1')
//     ->as('v1.')
//     ->prefix('category')
//     ->as('category.')
//     ->controller(CategoryController::class)
//     ->group(function () {
//         // Public routes - no authentication required
//         Route::get('/', 'index')->name('index');
//         Route::get('/{id}', 'detail')->name('detail');

//         // Protected routes - require authentication
//         Route::middleware(['auth:sanctum'])->group(function () {
//             Route::post('/', 'store')->name('store');
//             Route::put('/{id}', 'update')->name('update');
//             Route::delete('/{id}', 'destroy')->name('destroy');
//         });
//     });
