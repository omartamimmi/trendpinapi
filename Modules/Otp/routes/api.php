<?php

use Illuminate\Support\Facades\Route;
use Modules\Otp\app\Http\Controllers\OtpController;

Route::prefix('v1/otp')->group(function () {
    Route::post('/send', [OtpController::class, 'send'])->name('otp.send');
    Route::post('/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/check', [OtpController::class, 'check'])->name('otp.check');
});
