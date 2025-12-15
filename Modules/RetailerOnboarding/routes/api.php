<?php

use Illuminate\Support\Facades\Route;
use Modules\RetailerOnboarding\app\Http\Controllers\OnboardingController;
use Modules\RetailerOnboarding\app\Http\Controllers\Admin\SubscriptionPlanController;
use Modules\RetailerOnboarding\app\Http\Controllers\Admin\RetailerOnboardingController as AdminOnboardingController;

Route::prefix('v1/retailer-onboarding')->middleware('auth:sanctum')->group(function () {
    // Onboarding status and control
    Route::get('/status', [OnboardingController::class, 'status'])->name('onboarding.status');
    Route::post('/start', [OnboardingController::class, 'start'])->name('onboarding.start');

    // Step 1: Retailer Details (Phone Verification)
    Route::post('/phone/send-otp', [OnboardingController::class, 'sendPhoneOtp'])->name('onboarding.phone.send');
    Route::post('/phone/verify', [OnboardingController::class, 'verifyPhone'])->name('onboarding.phone.verify');

    // Step 2: Payment Details
    Route::post('/payment-methods', [OnboardingController::class, 'savePaymentMethods'])->name('onboarding.payment-methods.save');
    Route::post('/cliq/send-otp', [OnboardingController::class, 'sendCliqOtp'])->name('onboarding.cliq.send');
    Route::post('/cliq/verify', [OnboardingController::class, 'verifyCliq'])->name('onboarding.cliq.verify');
    Route::post('/payment-details/complete', [OnboardingController::class, 'completePaymentDetails'])->name('onboarding.payment-details.complete');

    // Step 3: Brand Information
    Route::post('/brands', [OnboardingController::class, 'saveBrandInfo'])->name('onboarding.brands.save');

    // Step 4: Subscription
    Route::get('/plans', [OnboardingController::class, 'getPlans'])->name('onboarding.plans.list');
    Route::post('/plans/select', [OnboardingController::class, 'selectPlan'])->name('onboarding.plans.select');

    // Step 5: Payment
    Route::post('/payment', [OnboardingController::class, 'processPayment'])->name('onboarding.payment.process');
});

// Admin routes
Route::prefix('v1/admin/retailer-onboarding')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Subscription Plans CRUD
    Route::get('/plans', [SubscriptionPlanController::class, 'index'])->name('admin.plans.index');
    Route::post('/plans', [SubscriptionPlanController::class, 'store'])->name('admin.plans.store');
    Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show'])->name('admin.plans.show');
    Route::put('/plans/{id}', [SubscriptionPlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('/plans/{id}', [SubscriptionPlanController::class, 'destroy'])->name('admin.plans.destroy');

    // Retailer Onboardings
    Route::get('/onboardings', [AdminOnboardingController::class, 'index'])->name('admin.onboardings.index');
    Route::get('/onboardings/{id}', [AdminOnboardingController::class, 'show'])->name('admin.onboardings.show');

    // Subscriptions
    Route::get('/subscriptions', [AdminOnboardingController::class, 'subscriptions'])->name('admin.subscriptions.index');

    // Payments
    Route::get('/payments', [AdminOnboardingController::class, 'payments'])->name('admin.payments.index');
    Route::post('/payments/{id}/approve', [AdminOnboardingController::class, 'approvePayment'])->name('admin.payments.approve');
    Route::post('/payments/{id}/reject', [AdminOnboardingController::class, 'rejectPayment'])->name('admin.payments.reject');
});
