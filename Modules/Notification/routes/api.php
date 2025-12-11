<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\app\Http\Controllers\AdminNotificationController;
use Modules\Notification\app\Http\Controllers\CustomerNotificationController;

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
