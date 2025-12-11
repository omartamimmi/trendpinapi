<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\app\Http\Controllers\AdminPageController;

// Redirect old admin login to unified login
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('/login', fn() => redirect('/login'));
});

// Protected admin routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminPageController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/', fn() => redirect()->route('admin.dashboard'));

    // Users
    Route::get('/users', [AdminPageController::class, 'users'])->name('admin.users');
    Route::post('/users', [AdminPageController::class, 'storeUser'])->name('admin.users.store.web');
    Route::put('/users/{id}', [AdminPageController::class, 'updateUser'])->name('admin.users.update.web');
    Route::delete('/users/{id}', [AdminPageController::class, 'destroyUser'])->name('admin.users.destroy.web');

    // Roles
    Route::get('/roles', [AdminPageController::class, 'roles'])->name('admin.roles');
    Route::post('/roles', [AdminPageController::class, 'storeRole'])->name('admin.roles.store.web');
    Route::put('/roles/{id}', [AdminPageController::class, 'updateRole'])->name('admin.roles.update.web');
    Route::delete('/roles/{id}', [AdminPageController::class, 'destroyRole'])->name('admin.roles.destroy.web');

    // Plans
    Route::get('/plans', [AdminPageController::class, 'plans'])->name('admin.plans');
    Route::post('/plans', [AdminPageController::class, 'storePlan'])->name('admin.plans.store');
    Route::put('/plans/{id}', [AdminPageController::class, 'updatePlan'])->name('admin.plans.update');
    Route::delete('/plans/{id}', [AdminPageController::class, 'destroyPlan'])->name('admin.plans.destroy');

    // Payments
    Route::get('/payments', [AdminPageController::class, 'payments'])->name('admin.payments');

    // Retailers
    Route::get('/retailers', [AdminPageController::class, 'retailers'])->name('admin.retailers');
    Route::get('/retailers/create', [AdminPageController::class, 'createRetailer'])->name('admin.retailers.create');
    Route::post('/retailers', [AdminPageController::class, 'storeRetailer'])->name('admin.retailers.store');
    Route::get('/retailers/{id}', [AdminPageController::class, 'showRetailer'])->name('admin.retailers.show');
    Route::put('/retailers/{id}', [AdminPageController::class, 'updateRetailer'])->name('admin.retailers.update');
    Route::delete('/retailers/{id}', [AdminPageController::class, 'destroyRetailer'])->name('admin.retailers.destroy');

    // Retailer Brands
    Route::get('/retailers/{retailerId}/brands', [AdminPageController::class, 'retailerBrands'])->name('admin.retailers.brands');
    Route::post('/retailers/{retailerId}/brands', [AdminPageController::class, 'storeRetailerBrand'])->name('admin.retailers.brands.store');
    Route::get('/brands/{id}/edit', [AdminPageController::class, 'editBrand'])->name('admin.brands.edit');
    Route::put('/brands/{id}', [AdminPageController::class, 'updateRetailerBrand'])->name('admin.brands.update');
    Route::delete('/brands/{id}', [AdminPageController::class, 'destroyRetailerBrand'])->name('admin.brands.destroy');

    // Groups
    Route::post('/groups', [AdminPageController::class, 'storeGroup'])->name('admin.groups.store');
    Route::put('/groups/{id}', [AdminPageController::class, 'updateGroup'])->name('admin.groups.update');
    Route::delete('/groups/{id}', [AdminPageController::class, 'destroyGroup'])->name('admin.groups.destroy');

    // Onboarding Approvals
    Route::get('/onboarding-approvals', [AdminPageController::class, 'onboardingApprovals'])->name('admin.onboarding-approvals');
    Route::get('/onboarding-approvals/{id}', [AdminPageController::class, 'showOnboardingReview'])->name('admin.onboarding-approvals.show');
    Route::post('/onboarding-approvals/{id}/approve', [AdminPageController::class, 'approveOnboarding'])->name('admin.onboarding-approvals.approve');
    Route::post('/onboarding-approvals/{id}/request-changes', [AdminPageController::class, 'requestOnboardingChanges'])->name('admin.onboarding-approvals.request-changes');
    Route::post('/onboarding-approvals/{id}/reject', [AdminPageController::class, 'rejectOnboarding'])->name('admin.onboarding-approvals.reject');

    // Categories
    Route::get('/categories', [AdminPageController::class, 'categories'])->name('admin.categories');
    Route::get('/categories/create', [AdminPageController::class, 'createCategory'])->name('admin.categories.create');
    Route::post('/categories', [AdminPageController::class, 'storeCategory'])->name('admin.categories.store');
    Route::get('/categories/{id}/edit', [AdminPageController::class, 'editCategory'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [AdminPageController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminPageController::class, 'destroyCategory'])->name('admin.categories.destroy');

    // Interests
    Route::get('/interests', [AdminPageController::class, 'interests'])->name('admin.interests');
    Route::get('/interests/create', [AdminPageController::class, 'createInterest'])->name('admin.interests.create');
    Route::post('/interests', [AdminPageController::class, 'storeInterest'])->name('admin.interests.store');
    Route::get('/interests/{id}/edit', [AdminPageController::class, 'editInterest'])->name('admin.interests.edit');
    Route::put('/interests/{id}', [AdminPageController::class, 'updateInterest'])->name('admin.interests.update');
    Route::delete('/interests/{id}', [AdminPageController::class, 'destroyInterest'])->name('admin.interests.destroy');

    // Notifications
    Route::get('/notifications', [AdminPageController::class, 'notifications'])->name('admin.notifications');
    Route::get('/notifications/send', [AdminPageController::class, 'sendNotificationPage'])->name('admin.notifications.send');
    Route::get('/notification-providers', [AdminPageController::class, 'notificationProviders'])->name('admin.notification-providers');
    Route::get('/notification-templates', [AdminPageController::class, 'notificationTemplates'])->name('admin.notification-templates');
});
