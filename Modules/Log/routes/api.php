<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\app\Http\Controllers\AdminLogController;

Route::prefix('v1/admin')
    ->middleware(['api', 'auth:sanctum', 'role:admin'])
    ->group(function () {
        // Log management endpoints
        Route::prefix('logs')->group(function () {
            Route::get('/', [AdminLogController::class, 'index'])->name('admin.logs.index');
            Route::get('/stats', [AdminLogController::class, 'stats'])->name('admin.logs.stats');
            Route::get('/recent-errors', [AdminLogController::class, 'recentErrors'])->name('admin.logs.recent-errors');
            Route::get('/filter-options', [AdminLogController::class, 'filterOptions'])->name('admin.logs.filter-options');
            Route::get('/request/{requestId}', [AdminLogController::class, 'requestLogs'])->name('admin.logs.request');
            Route::get('/user/{userId}', [AdminLogController::class, 'userActivity'])->name('admin.logs.user');
            Route::get('/{id}', [AdminLogController::class, 'show'])->name('admin.logs.show');
            Route::post('/cleanup', [AdminLogController::class, 'cleanup'])->name('admin.logs.cleanup');
        });
    });
