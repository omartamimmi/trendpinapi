<?php

use Illuminate\Support\Facades\Route;

// Business web routes - managed via RetailerOnboarding and Admin modules
Route::middleware(['auth', 'verified'])->group(function () {
    // Brand routes are now handled by RetailerPageController
});
