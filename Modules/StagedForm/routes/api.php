<?php

use Illuminate\Support\Facades\Route;
use Modules\StagedForm\app\Http\Controllers\StagedFormController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('stagedforms')->as('stagedforms.')->group(function (){
        Route::prefix('step')->as('step.')->group(function (){
            Route::get('get', [StagedFormController::class, 'get'])->name('get');
            Route::post('store', [StagedFormController::class, 'store'])->name('store');
        });
    });
});