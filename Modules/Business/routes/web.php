<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\app\Http\Controllers\BusinessController;

Route::middleware(['auth', 'verified', 'retailer.approved'])->group(function () {
    Route::resource('businesses', BusinessController::class)->names('business');
});
