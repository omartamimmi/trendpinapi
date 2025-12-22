<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\app\Http\Controllers\Api\RetailerQrSessionController;
use Modules\Payment\app\Http\Controllers\Api\CustomerPaymentController;
use Modules\Payment\app\Http\Controllers\Admin\PaymentSettingsController;
use Modules\Payment\app\Http\Controllers\Admin\PaymentAnalyticsController;
use Modules\Payment\app\Http\Controllers\Webhook\TapPaymentsWebhookController;
use Modules\Payment\app\Http\Controllers\Webhook\CliqWebhookController;

/*
|--------------------------------------------------------------------------
| Payment API Routes
|--------------------------------------------------------------------------
|
| QR-based payment system routes for TrendPin
|
*/

// ========================================
// RETAILER APP ROUTES
// ========================================
Route::prefix('v1/retailer')->middleware(['auth:sanctum'])->group(function () {
    // QR Payment Sessions
    Route::prefix('qr-sessions')->group(function () {
        // Create new QR payment session
        Route::post('/', [RetailerQrSessionController::class, 'store'])
            ->name('retailer.qr-sessions.store');

        // List all sessions (history)
        Route::get('/', [RetailerQrSessionController::class, 'index'])
            ->name('retailer.qr-sessions.index');

        // Get session statistics
        Route::get('/stats', [RetailerQrSessionController::class, 'stats'])
            ->name('retailer.qr-sessions.stats');

        // Get session details
        Route::get('/{code}', [RetailerQrSessionController::class, 'show'])
            ->name('retailer.qr-sessions.show');

        // Get session status (for polling)
        Route::get('/{code}/status', [RetailerQrSessionController::class, 'status'])
            ->name('retailer.qr-sessions.status');

        // Cancel session
        Route::post('/{code}/cancel', [RetailerQrSessionController::class, 'cancel'])
            ->name('retailer.qr-sessions.cancel');
    });
});

// ========================================
// CUSTOMER APP ROUTES
// ========================================
Route::prefix('v1/customer')->middleware(['auth:sanctum'])->group(function () {
    // QR Payment Sessions
    Route::prefix('qr-sessions/{code}')->group(function () {
        // Scan QR code
        Route::post('/scan', [CustomerPaymentController::class, 'scan'])
            ->name('customer.qr-sessions.scan');

        // Calculate discount for selected card
        Route::post('/calculate-discount', [CustomerPaymentController::class, 'calculateDiscount'])
            ->name('customer.qr-sessions.calculate-discount');

        // Pay with new card (3DS redirect)
        Route::post('/pay', [CustomerPaymentController::class, 'pay'])
            ->name('customer.qr-sessions.pay');

        // Pay with saved card (one-tap)
        Route::post('/pay-with-saved-card', [CustomerPaymentController::class, 'payWithSavedCard'])
            ->name('customer.qr-sessions.pay-saved-card');

        // Pay with Apple Pay / Google Pay
        Route::post('/pay-with-wallet', [CustomerPaymentController::class, 'payWithWallet'])
            ->name('customer.qr-sessions.pay-wallet');

        // Pay with CliQ
        Route::post('/pay-with-cliq', [CustomerPaymentController::class, 'payWithCliq'])
            ->name('customer.qr-sessions.pay-cliq');
    });

    // Saved Cards Management
    Route::prefix('cards')->group(function () {
        // List saved cards
        Route::get('/', [CustomerPaymentController::class, 'listCards'])
            ->name('customer.cards.index');

        // Save new card
        Route::post('/', [CustomerPaymentController::class, 'saveCard'])
            ->name('customer.cards.store');

        // Delete card
        Route::delete('/{id}', [CustomerPaymentController::class, 'deleteCard'])
            ->name('customer.cards.destroy');

        // Set default card
        Route::post('/{id}/set-default', [CustomerPaymentController::class, 'setDefaultCard'])
            ->name('customer.cards.set-default');
    });

    // Payment History
    Route::prefix('payments')->group(function () {
        // List payment history
        Route::get('/', [CustomerPaymentController::class, 'paymentHistory'])
            ->name('customer.payments.index');

        // Get payment details
        Route::get('/{id}', [CustomerPaymentController::class, 'paymentDetails'])
            ->name('customer.payments.show');
    });
});

// ========================================
// ADMIN ROUTES
// ========================================
Route::prefix('v1/admin/payment')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Settings Overview
    Route::get('/settings', [PaymentSettingsController::class, 'index'])
        ->name('admin.payment.settings.index');

    // Gateway Management
    Route::prefix('gateways')->group(function () {
        // List all gateways
        Route::get('/', [PaymentSettingsController::class, 'listGateways'])
            ->name('admin.payment.gateways.index');

        // Get gateway details
        Route::get('/{gateway}', [PaymentSettingsController::class, 'showGateway'])
            ->name('admin.payment.gateways.show');

        // Update gateway settings
        Route::put('/{gateway}', [PaymentSettingsController::class, 'updateGateway'])
            ->name('admin.payment.gateways.update');

        // Test gateway connection
        Route::post('/{gateway}/test', [PaymentSettingsController::class, 'testGateway'])
            ->name('admin.payment.gateways.test');
    });

    // Payment Methods Management
    Route::prefix('methods')->group(function () {
        // List all payment methods
        Route::get('/', [PaymentSettingsController::class, 'listMethods'])
            ->name('admin.payment.methods.index');

        // Update payment method settings
        Route::put('/{method}', [PaymentSettingsController::class, 'updateMethod'])
            ->name('admin.payment.methods.update');

        // Toggle payment method
        Route::post('/{method}/toggle', [PaymentSettingsController::class, 'toggleMethod'])
            ->name('admin.payment.methods.toggle');
    });

    // General Settings
    Route::put('/settings/general', [PaymentSettingsController::class, 'updateGeneral'])
        ->name('admin.payment.settings.general');

    // Fee Configuration
    Route::get('/fees', [PaymentSettingsController::class, 'getFees'])
        ->name('admin.payment.fees');

    // Analytics Routes
    Route::prefix('analytics')->group(function () {
        // Dashboard overview
        Route::get('/dashboard', [PaymentAnalyticsController::class, 'dashboard'])
            ->name('admin.payment.analytics.dashboard');

        // Transaction list
        Route::get('/transactions', [PaymentAnalyticsController::class, 'transactions'])
            ->name('admin.payment.analytics.transactions');

        // Analytics by gateway
        Route::get('/by-gateway', [PaymentAnalyticsController::class, 'byGateway'])
            ->name('admin.payment.analytics.by-gateway');

        // Analytics by payment method
        Route::get('/by-method', [PaymentAnalyticsController::class, 'byMethod'])
            ->name('admin.payment.analytics.by-method');

        // Analytics by brand/retailer
        Route::get('/by-brand', [PaymentAnalyticsController::class, 'byBrand'])
            ->name('admin.payment.analytics.by-brand');

        // Analytics by branch
        Route::get('/by-branch', [PaymentAnalyticsController::class, 'byBranch'])
            ->name('admin.payment.analytics.by-branch');

        // Analytics by bank (discounts)
        Route::get('/by-bank', [PaymentAnalyticsController::class, 'byBank'])
            ->name('admin.payment.analytics.by-bank');

        // Payment trends
        Route::get('/trends', [PaymentAnalyticsController::class, 'trends'])
            ->name('admin.payment.analytics.trends');

        // Conversion analytics
        Route::get('/conversion', [PaymentAnalyticsController::class, 'conversion'])
            ->name('admin.payment.analytics.conversion');

        // Customer analytics
        Route::get('/customers', [PaymentAnalyticsController::class, 'customers'])
            ->name('admin.payment.analytics.customers');

        // Export data
        Route::get('/export', [PaymentAnalyticsController::class, 'export'])
            ->name('admin.payment.analytics.export');
    });
});

// ========================================
// WEBHOOK ROUTES (No Auth)
// ========================================
Route::prefix('webhooks/payment')->group(function () {
    // Tap Payments webhook
    Route::post('/tap', [TapPaymentsWebhookController::class, 'handle'])
        ->name('webhooks.payment.tap');

    // CliQ/JOPACC webhook
    Route::post('/cliq', [CliqWebhookController::class, 'handle'])
        ->name('webhooks.payment.cliq');
});
