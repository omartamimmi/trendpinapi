<?php

use Illuminate\Support\Facades\Route;
use Modules\Geofence\app\Http\Controllers\RadarWebhookController;
use Modules\Geofence\app\Http\Middleware\VerifyRadarSignature;

/*
|--------------------------------------------------------------------------
| Geofence Webhook Routes
|--------------------------------------------------------------------------
|
| Here is where you can register webhook routes for the Geofence module.
| These routes handle incoming webhooks from Radar.io and other services.
|
*/

Route::prefix('radar')->name('radar.')->group(function () {
    // Webhook endpoint for Radar.io events
    Route::post('/', [RadarWebhookController::class, 'handle'])
        ->middleware(VerifyRadarSignature::class)
        ->name('webhook');

    // Verification endpoint
    Route::get('/verify', [RadarWebhookController::class, 'verify'])
        ->name('verify');
});
