<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\app\Http\Controllers\AuthController;
use Modules\Admin\app\Http\Controllers\UserController;
use Modules\Admin\app\Http\Controllers\RoleController;
use Modules\Admin\app\Http\Controllers\DashboardController;

// Note: Login is handled by main API routes at POST /api/v1/login
// This provides a unified authentication endpoint for all user types

// Protected admin routes (require authentication + admin role)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('admin.me');

    // Dashboard
    Route::get('/dashboard/statistics', [DashboardController::class, 'statistics'])->name('admin.dashboard.stats');
    Route::get('/dashboard/activities', [DashboardController::class, 'recentActivities'])->name('admin.dashboard.activities');

    // Users CRUD
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Roles CRUD
    Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('admin.roles.show');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

    // Permissions
    Route::get('/permissions', [RoleController::class, 'permissions'])->name('admin.permissions.index');
    Route::post('/permissions', [RoleController::class, 'createPermission'])->name('admin.permissions.store');
});
