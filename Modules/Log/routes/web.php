<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\app\Http\Controllers\AdminLogPageController;

// Admin Log Pages
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/logs', [AdminLogPageController::class, 'index'])->name('admin.logs');
    Route::get('/logs/{id}', [AdminLogPageController::class, 'show'])->name('admin.logs.show');
});
