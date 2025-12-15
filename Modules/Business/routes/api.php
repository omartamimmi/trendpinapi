<?php

use Illuminate\Support\Facades\Route;

// Business API routes - managed via RetailerOnboarding and Admin modules
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Brand routes are now handled by RetailerOnboarding module
});
