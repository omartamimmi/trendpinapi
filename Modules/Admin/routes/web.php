<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\app\Http\Controllers\AdminDashboardController;
use Modules\Admin\app\Http\Controllers\AdminUserController;
use Modules\Admin\app\Http\Controllers\AdminRoleController;
use Modules\Admin\app\Http\Controllers\AdminPlanController;
use Modules\Admin\app\Http\Controllers\AdminPaymentController;
use Modules\Admin\app\Http\Controllers\AdminRetailerController;
use Modules\Admin\app\Http\Controllers\AdminOnboardingController;
use Modules\Admin\app\Http\Controllers\AdminCategoryController;
use Modules\Admin\app\Http\Controllers\AdminInterestController;
use Modules\Admin\app\Http\Controllers\AdminNotificationPageController;
use Modules\Admin\app\Http\Controllers\AdminOfferController;
use Modules\Admin\app\Http\Controllers\AdminBankOfferPageController;
use Modules\Admin\app\Http\Controllers\AdminQrPaymentController;

// Redirect old admin login to unified login
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('/login', fn() => redirect('/login'));
});

// Protected admin routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/', fn() => redirect()->route('admin.dashboard'));

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store.web');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update.web');
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy.web');

    // Roles
    Route::get('/roles', [AdminRoleController::class, 'index'])->name('admin.roles');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('admin.roles.store.web');
    Route::put('/roles/{id}', [AdminRoleController::class, 'update'])->name('admin.roles.update.web');
    Route::delete('/roles/{id}', [AdminRoleController::class, 'destroy'])->name('admin.roles.destroy.web');

    // Plans
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('admin.plans');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('admin.plans.store');
    Route::put('/plans/{id}', [AdminPlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('/plans/{id}', [AdminPlanController::class, 'destroy'])->name('admin.plans.destroy');

    // Payments
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('admin.payments');

    // Retailers
    Route::get('/retailers', [AdminRetailerController::class, 'index'])->name('admin.retailers');
    Route::get('/retailers/create', [AdminRetailerController::class, 'create'])->name('admin.retailers.create');
    Route::post('/retailers', [AdminRetailerController::class, 'store'])->name('admin.retailers.store');
    Route::get('/retailers/{id}', [AdminRetailerController::class, 'show'])->name('admin.retailers.show');
    Route::put('/retailers/{id}', [AdminRetailerController::class, 'update'])->name('admin.retailers.update');
    Route::delete('/retailers/{id}', [AdminRetailerController::class, 'destroy'])->name('admin.retailers.destroy');

    // Retailer Brands
    Route::get('/retailers/{retailerId}/brands', [AdminRetailerController::class, 'brands'])->name('admin.retailers.brands');
    Route::post('/retailers/{retailerId}/brands', [AdminRetailerController::class, 'storeBrand'])->name('admin.retailers.brands.store');
    Route::get('/brands/{id}/edit', [AdminRetailerController::class, 'editBrand'])->name('admin.brands.edit');
    Route::put('/brands/{id}', [AdminRetailerController::class, 'updateBrand'])->name('admin.brands.update');
    Route::delete('/brands/{id}', [AdminRetailerController::class, 'destroyBrand'])->name('admin.brands.destroy');

    // Onboarding Approvals
    Route::get('/onboarding-approvals', [AdminOnboardingController::class, 'index'])->name('admin.onboarding-approvals');
    Route::get('/onboarding-approvals/{id}', [AdminOnboardingController::class, 'show'])->name('admin.onboarding-approvals.show');
    Route::get('/onboarding-approvals/{id}/edit', [AdminOnboardingController::class, 'edit'])->name('admin.onboarding-approvals.edit');
    Route::post('/onboarding-approvals/{id}/approve', [AdminOnboardingController::class, 'approve'])->name('admin.onboarding-approvals.approve');
    Route::post('/onboarding-approvals/{id}/request-changes', [AdminOnboardingController::class, 'requestChanges'])->name('admin.onboarding-approvals.request-changes');
    Route::post('/onboarding-approvals/{id}/reject', [AdminOnboardingController::class, 'reject'])->name('admin.onboarding-approvals.reject');

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories');
    Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Interests
    Route::get('/interests', [AdminInterestController::class, 'index'])->name('admin.interests');
    Route::get('/interests/create', [AdminInterestController::class, 'create'])->name('admin.interests.create');
    Route::post('/interests', [AdminInterestController::class, 'store'])->name('admin.interests.store');
    Route::get('/interests/{id}/edit', [AdminInterestController::class, 'edit'])->name('admin.interests.edit');
    Route::put('/interests/{id}', [AdminInterestController::class, 'update'])->name('admin.interests.update');
    Route::delete('/interests/{id}', [AdminInterestController::class, 'destroy'])->name('admin.interests.destroy');

    // Notifications
    Route::get('/notifications', [AdminNotificationPageController::class, 'index'])->name('admin.notifications');
    Route::get('/notifications/send', [AdminNotificationPageController::class, 'send'])->name('admin.notifications.send');
    Route::get('/notification-providers', [AdminNotificationPageController::class, 'providers'])->name('admin.notification-providers');
    Route::get('/notification-templates', [AdminNotificationPageController::class, 'templates'])->name('admin.notification-templates');
    Route::get('/notification-settings', [AdminNotificationPageController::class, 'settings'])->name('admin.notification-settings');
    Route::get('/notification-credentials', [AdminNotificationPageController::class, 'credentials'])->name('admin.notification-credentials');

    // Offers
    Route::get('/offers', [AdminOfferController::class, 'index'])->name('admin.offers');
    Route::get('/offers/create', [AdminOfferController::class, 'create'])->name('admin.offers.create');
    Route::post('/offers', [AdminOfferController::class, 'store'])->name('admin.offers.store');
    Route::get('/offers/{id}/edit', [AdminOfferController::class, 'edit'])->name('admin.offers.edit');
    Route::put('/offers/{id}', [AdminOfferController::class, 'update'])->name('admin.offers.update');
    Route::delete('/offers/{id}', [AdminOfferController::class, 'destroy'])->name('admin.offers.destroy');
    Route::get('/offers/brands/{retailerId}', [AdminOfferController::class, 'getBrands'])->name('admin.offers.brands');

    // Bank Offers Module
    Route::prefix('bank-offer')->group(function () {
        // Banks
        Route::get('/banks', [AdminBankOfferPageController::class, 'banks'])->name('admin.bank-offer.banks');
        Route::get('/banks/create', [AdminBankOfferPageController::class, 'createBank'])->name('admin.bank-offer.banks.create');
        Route::post('/banks', [AdminBankOfferPageController::class, 'storeBank'])->name('admin.bank-offer.banks.store');
        Route::get('/banks/{id}/edit', [AdminBankOfferPageController::class, 'editBank'])->name('admin.bank-offer.banks.edit');
        Route::put('/banks/{id}', [AdminBankOfferPageController::class, 'updateBank'])->name('admin.bank-offer.banks.update');
        Route::delete('/banks/{id}', [AdminBankOfferPageController::class, 'destroyBank'])->name('admin.bank-offer.banks.destroy');

        // Card Types
        Route::get('/card-types', [AdminBankOfferPageController::class, 'cardTypes'])->name('admin.bank-offer.card-types');
        Route::get('/card-types/create', [AdminBankOfferPageController::class, 'createCardType'])->name('admin.bank-offer.card-types.create');
        Route::post('/card-types', [AdminBankOfferPageController::class, 'storeCardType'])->name('admin.bank-offer.card-types.store');
        Route::get('/card-types/{id}/edit', [AdminBankOfferPageController::class, 'editCardType'])->name('admin.bank-offer.card-types.edit');
        Route::put('/card-types/{id}', [AdminBankOfferPageController::class, 'updateCardType'])->name('admin.bank-offer.card-types.update');
        Route::delete('/card-types/{id}', [AdminBankOfferPageController::class, 'destroyCardType'])->name('admin.bank-offer.card-types.destroy');

        // Bank Offers
        Route::get('/offers', [AdminBankOfferPageController::class, 'offers'])->name('admin.bank-offer.offers');
        Route::get('/offers/{id}', [AdminBankOfferPageController::class, 'showOffer'])->name('admin.bank-offer.offers.show');
        Route::put('/offers/{id}/approve', [AdminBankOfferPageController::class, 'approveOffer'])->name('admin.bank-offer.offers.approve');
        Route::put('/offers/{id}/reject', [AdminBankOfferPageController::class, 'rejectOffer'])->name('admin.bank-offer.offers.reject');
        Route::put('/offers/{id}/status', [AdminBankOfferPageController::class, 'updateOfferStatus'])->name('admin.bank-offer.offers.status');

        // Participation Requests
        Route::get('/requests', [AdminBankOfferPageController::class, 'requests'])->name('admin.bank-offer.requests');
        Route::put('/requests/{id}/approve', [AdminBankOfferPageController::class, 'approveRequest'])->name('admin.bank-offer.requests.approve');
        Route::put('/requests/{id}/reject', [AdminBankOfferPageController::class, 'rejectRequest'])->name('admin.bank-offer.requests.reject');
    });

    // QR Payment System
    Route::prefix('qr-payment')->group(function () {
        // Settings (gateways & methods)
        Route::get('/settings', [AdminQrPaymentController::class, 'settings'])->name('admin.qr-payment.settings');
        Route::put('/gateways/{gateway}', [AdminQrPaymentController::class, 'updateGateway'])->name('admin.qr-payment.gateways.update');
        Route::post('/gateways/{gateway}/test', [AdminQrPaymentController::class, 'testGateway'])->name('admin.qr-payment.gateways.test');
        Route::post('/methods/{method}/toggle', [AdminQrPaymentController::class, 'toggleMethod'])->name('admin.qr-payment.methods.toggle');
        Route::put('/settings/general', [AdminQrPaymentController::class, 'updateGeneralSettings'])->name('admin.qr-payment.settings.general');

        // Analytics
        Route::get('/analytics', [AdminQrPaymentController::class, 'analytics'])->name('admin.qr-payment.analytics');

        // Transactions
        Route::get('/transactions', [AdminQrPaymentController::class, 'transactions'])->name('admin.qr-payment.transactions');
        Route::get('/transactions/{id}', [AdminQrPaymentController::class, 'transactionDetails'])->name('admin.qr-payment.transactions.show');

        // Sessions
        Route::get('/sessions', [AdminQrPaymentController::class, 'sessions'])->name('admin.qr-payment.sessions');
    });
});
