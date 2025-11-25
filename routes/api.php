<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MerchantQrPaymentController;
use App\Http\Controllers\Api\CustomerQrPaymentController;

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

// Public API routes
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// API Version 1
Route::prefix('v1')->group(function () {

    // Public authentication routes
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');

    // Protected API routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        // Merchant QR Payment Routes (for retailers)
        Route::prefix('merchant/qr-payments')->group(function () {
            Route::post('/generate', [MerchantQrPaymentController::class, 'generate'])->name('api.v1.merchant.qr.generate');
            Route::get('/', [MerchantQrPaymentController::class, 'index'])->name('api.v1.merchant.qr.index');
            Route::get('/{id}', [MerchantQrPaymentController::class, 'show'])->name('api.v1.merchant.qr.show');
            Route::post('/{id}/cancel', [MerchantQrPaymentController::class, 'cancel'])->name('api.v1.merchant.qr.cancel');
            Route::get('/status/{reference}', [MerchantQrPaymentController::class, 'checkStatus'])->name('api.v1.merchant.qr.status');
        });

        // Customer QR Payment Routes (for customers)
        Route::prefix('customer/qr-payments')->group(function () {
            Route::post('/verify', [CustomerQrPaymentController::class, 'verify'])->name('api.v1.customer.qr.verify');
            Route::post('/pay', [CustomerQrPaymentController::class, 'pay'])->name('api.v1.customer.qr.pay');
            Route::get('/history', [CustomerQrPaymentController::class, 'history'])->name('api.v1.customer.qr.history');
            Route::get('/{reference}', [CustomerQrPaymentController::class, 'show'])->name('api.v1.customer.qr.show');
        });
    });
});
