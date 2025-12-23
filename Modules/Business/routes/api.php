<?php

use Illuminate\Support\Facades\Route;
use Modules\Business\app\Http\Controllers\BrandController;
use Modules\Business\app\Http\Controllers\HomeController;

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Home page endpoint
    Route::get('/home', [HomeController::class, 'index'])->name('api.v1.home');

    // Brand routes
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('api.v1.brands.index');
        Route::get('/slug/{slug}', [BrandController::class, 'showBySlug'])->name('api.v1.brands.show.slug');
        Route::get('/{id}', [BrandController::class, 'show'])->name('api.v1.brands.show');
        Route::get('/{brandId}/branches/{branchId}', [BrandController::class, 'showBranch'])->name('api.v1.brands.branch.show');
    });
});
