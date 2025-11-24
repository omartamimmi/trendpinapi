<?php

use Illuminate\Support\Facades\Route;
use Modules\RetailerOnboarding\app\Http\Controllers\RetailerPageController;
use Modules\RetailerOnboarding\app\Http\Controllers\OnboardingController;

// Guest routes (register only - login is unified)
Route::prefix('retailer')->middleware('guest')->group(function () {
    // Redirect old login to unified login
    Route::get('/login', fn() => redirect('/login'));

    // Keep retailer registration separate
    Route::get('/register', [RetailerPageController::class, 'registerPage'])->name('retailer.register.page');
    Route::post('/register', [RetailerPageController::class, 'register'])->name('retailer.register.submit');
});

// Protected retailer routes
Route::prefix('retailer')->middleware(['auth', 'role:retailer'])->group(function () {

    // Onboarding page (accessible without completion)
    Route::get('/onboarding', [RetailerPageController::class, 'onboarding'])->name('retailer.onboarding');

    // Onboarding API (web session auth)
    Route::prefix('onboarding')->group(function () {
        Route::post('/start', [OnboardingController::class, 'start']);
        Route::post('/phone/send-otp', [OnboardingController::class, 'sendPhoneOtp']);
        Route::post('/phone/verify', [OnboardingController::class, 'verifyPhone']);
        Route::post('/payment-methods', [OnboardingController::class, 'savePaymentMethods']);
        Route::post('/cliq/send-otp', [OnboardingController::class, 'sendCliqOtp']);
        Route::post('/cliq/verify', [OnboardingController::class, 'verifyCliq']);
        Route::post('/payment-details/complete', [OnboardingController::class, 'completePaymentDetails']);
        Route::post('/brands', [OnboardingController::class, 'saveBrandInfo']);
        Route::get('/plans', [OnboardingController::class, 'getPlans']);
        Route::post('/plans/select', [OnboardingController::class, 'selectPlan']);
        Route::post('/payment', [OnboardingController::class, 'processPayment']);
    });

    // Dashboard and other pages (require onboarding completion)
    Route::middleware(['onboarding.complete'])->group(function () {
        Route::get('/dashboard', [RetailerPageController::class, 'dashboard'])->name('retailer.dashboard');
        Route::get('/', fn() => redirect()->route('retailer.dashboard'));

        // Settings
        Route::get('/settings', [RetailerPageController::class, 'settings'])->name('retailer.settings');
        Route::put('/settings/profile', [RetailerPageController::class, 'updateProfile'])->name('retailer.settings.profile');
        Route::put('/settings/password', [RetailerPageController::class, 'updatePassword'])->name('retailer.settings.password');

        // Brands
        Route::get('/brands', [RetailerPageController::class, 'brands'])->name('retailer.brands');
        Route::get('/brands/create', [RetailerPageController::class, 'createBrand'])->name('retailer.brands.create');
        Route::post('/brands', [RetailerPageController::class, 'storeBrand'])->name('retailer.brands.store');
        Route::get('/brands/{id}/edit', [RetailerPageController::class, 'editBrand'])->name('retailer.brands.edit');
        Route::put('/brands/{id}', [RetailerPageController::class, 'updateBrand'])->name('retailer.brands.update');
        Route::delete('/brands/{id}', [RetailerPageController::class, 'destroyBrand'])->name('retailer.brands.destroy');

        // Offers
        Route::get('/offers', [RetailerPageController::class, 'offers'])->name('retailer.offers');
        Route::get('/offers/create', [RetailerPageController::class, 'createOffer'])->name('retailer.offers.create');
        Route::post('/offers', [RetailerPageController::class, 'storeOffer'])->name('retailer.offers.store');
        Route::get('/offers/{id}/edit', [RetailerPageController::class, 'editOffer'])->name('retailer.offers.edit');
        Route::put('/offers/{id}', [RetailerPageController::class, 'updateOffer'])->name('retailer.offers.update');
        Route::delete('/offers/{id}', [RetailerPageController::class, 'destroyOffer'])->name('retailer.offers.destroy');
    });
});
