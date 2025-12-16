<?php

use Illuminate\Support\Facades\Route;
use Modules\Geofence\app\Http\Controllers\AdminGeofenceController;
use Modules\Geofence\app\Http\Controllers\AdminLocationController;

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

    // Locations CRUD
    Route::get('/locations', [AdminLocationController::class, 'index'])->name('locations');
    Route::get('/locations/all-branches', [AdminLocationController::class, 'allBranches'])->name('locations.all-branches');
    Route::get('/locations/{id}', [AdminLocationController::class, 'show'])->name('locations.show');
    Route::post('/locations', [AdminLocationController::class, 'store'])->name('locations.store');
    Route::put('/locations/{id}', [AdminLocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{id}', [AdminLocationController::class, 'destroy'])->name('locations.destroy');
    Route::get('/locations/{id}/branches', [AdminLocationController::class, 'branches'])->name('locations.branches');
    Route::post('/locations/{id}/branches', [AdminLocationController::class, 'assignBranches'])->name('locations.assign-branches');
    Route::post('/locations/{id}/sync', [AdminLocationController::class, 'sync'])->name('locations.sync');
});
