<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\app\Http\Controllers\AdminNotificationController;
use Modules\Notification\app\Http\Controllers\CustomerNotificationController;
use Modules\Notification\app\Http\Controllers\NotificationCredentialController;
use Modules\Notification\app\Http\Controllers\NotificationSettingsController;

// Admin Routes (protected by auth:sanctum and admin middleware)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Notification Providers
    Route::get('notification-providers', [AdminNotificationController::class, 'getProviders']);
    Route::post('notification-providers', [AdminNotificationController::class, 'storeProvider']);
    Route::put('notification-providers/{id}', [AdminNotificationController::class, 'updateProvider']);
    Route::post('notification-providers/{id}/test', [AdminNotificationController::class, 'testProvider']);
    Route::delete('notification-providers/{id}', [AdminNotificationController::class, 'deleteProvider']);

    // Notification Messages
    Route::post('notifications/send', [AdminNotificationController::class, 'sendNotification']);
    Route::get('notifications', [AdminNotificationController::class, 'getNotifications']);
    Route::get('notifications/{id}', [AdminNotificationController::class, 'getNotification']);
    Route::get('notifications/{id}/stats', [AdminNotificationController::class, 'getNotificationStats']);

    // Notification Templates
    Route::get('notification-templates', [AdminNotificationController::class, 'getTemplates']);
    Route::post('notification-templates', [AdminNotificationController::class, 'storeTemplate']);
    Route::put('notification-templates/{id}', [AdminNotificationController::class, 'updateTemplate']);
    Route::delete('notification-templates/{id}', [AdminNotificationController::class, 'deleteTemplate']);

    // Notification Credentials Management
    Route::prefix('notification-credentials')->group(function () {
        // Get all credential statuses
        Route::get('statuses', [NotificationCredentialController::class, 'getStatuses']);

        // Get all credentials (masked)
        Route::get('/', [NotificationCredentialController::class, 'getAllCredentials']);

        // Get providers for a channel
        Route::get('providers/{channel}', [NotificationCredentialController::class, 'getProviders']);

        // Channel-specific credentials
        Route::get('{channel}', [NotificationCredentialController::class, 'getCredentials']);
        Route::post('{channel}', [NotificationCredentialController::class, 'saveCredentials']);
        Route::delete('{channel}', [NotificationCredentialController::class, 'deleteCredentials']);
        Route::post('{channel}/test', [NotificationCredentialController::class, 'testCredentials']);
        Route::post('{channel}/toggle', [NotificationCredentialController::class, 'toggleActive']);
    });

    // Notification Testing
    Route::prefix('notification-test')->group(function () {
        // Get available test events
        Route::get('events', [NotificationCredentialController::class, 'getTestEvents']);

        // Get placeholders for an event
        Route::get('events/{eventId}/placeholders', [NotificationCredentialController::class, 'getEventPlaceholders']);

        // Get recipients by type
        Route::get('recipients/{type}', [NotificationCredentialController::class, 'getRecipients']);

        // Send test notification
        Route::post('send', [NotificationCredentialController::class, 'sendTest']);
    });

    // Notification Settings (Events & Templates)
    Route::prefix('notification-settings')->group(function () {
        // Get all settings
        Route::get('/', [NotificationSettingsController::class, 'index']);

        // Save all settings (bulk)
        Route::post('/', [NotificationSettingsController::class, 'store']);

        // Get single setting
        Route::get('{eventId}', [NotificationSettingsController::class, 'show']);

        // Update single setting
        Route::put('{eventId}', [NotificationSettingsController::class, 'update']);

        // Toggle enabled status
        Route::post('{eventId}/toggle', [NotificationSettingsController::class, 'toggleEnabled']);
    });
});

// Customer Routes (protected by auth:sanctum)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // User Notifications
    Route::get('notifications', [CustomerNotificationController::class, 'getNotifications']);
    Route::get('notifications/unread-count', [CustomerNotificationController::class, 'getUnreadCount']);
    Route::get('notifications/{id}', [CustomerNotificationController::class, 'getNotification']);
    Route::post('notifications/{id}/mark-read', [CustomerNotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [CustomerNotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [CustomerNotificationController::class, 'deleteNotification']);

    // User Preferences
    Route::get('user/notification-preferences', [CustomerNotificationController::class, 'getPreferences']);
    Route::put('user/notification-preferences', [CustomerNotificationController::class, 'updatePreferences']);

    // FCM Token
    Route::post('user/fcm-token', [CustomerNotificationController::class, 'updateFCMToken']);
});
