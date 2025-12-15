<?php

use Illuminate\Support\Facades\Route;
use Modules\Geofence\app\Http\Controllers\AdminGeofenceController;

/*
|--------------------------------------------------------------------------
| Geofence Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Geofence module.
| These routes are loaded by the RouteServiceProvider.
|
*/

// Admin routes for geofence management
Route::prefix('admin/geofence')->middleware(['auth', 'role:admin'])->name('admin.geofence.')->group(function () {
    // Dashboard
    Route::get('/', [AdminGeofenceController::class, 'index'])->name('dashboard');

    // Geofences CRUD
    Route::get('/geofences', [AdminGeofenceController::class, 'geofences'])->name('geofences');
    Route::post('/geofences', [AdminGeofenceController::class, 'store'])->name('geofences.store');
    Route::put('/geofences/{id}', [AdminGeofenceController::class, 'update'])->name('geofences.update');
    Route::delete('/geofences/{id}', [AdminGeofenceController::class, 'destroy'])->name('geofences.destroy');

    // Notification Logs
    Route::get('/notifications', [AdminGeofenceController::class, 'notifications'])->name('notifications');

    // Settings
    Route::get('/settings', [AdminGeofenceController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminGeofenceController::class, 'updateSettings'])->name('settings.update');

    // Sync to Radar
    Route::post('/sync', [AdminGeofenceController::class, 'sync'])->name('sync');

    // Testing & Simulation
    Route::get('/test', [AdminGeofenceController::class, 'test'])->name('test');
    Route::post('/test/simulate', [AdminGeofenceController::class, 'simulateEvent'])->name('test.simulate');
    Route::post('/test/eligibility', [AdminGeofenceController::class, 'checkEligibility'])->name('test.eligibility');
});
