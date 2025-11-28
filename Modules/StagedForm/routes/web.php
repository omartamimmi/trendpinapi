<?php

use Illuminate\Support\Facades\Route;
use Modules\StagedForm\app\Http\Controllers\StagedFormController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('stagedforms', StagedFormController::class)->names('stagedform');
});
