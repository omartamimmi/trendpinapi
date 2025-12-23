<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Cloud Messaging v1 API
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file, or the JSON content itself
    | Download from: Firebase Console -> Project Settings -> Service Accounts
    |
    */
    'credentials' => [
        // Option 1: Path to JSON file
        'file' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/service-account.json')),

        // Option 2: JSON content directly (for environments where file storage is not ideal)
        'json' => env('FIREBASE_CREDENTIALS_JSON'),
    ],

    /*
    |--------------------------------------------------------------------------
    | FCM API Settings
    |--------------------------------------------------------------------------
    */
    'fcm' => [
        // FCM v1 API endpoint (auto-configured with project_id)
        'endpoint' => 'https://fcm.googleapis.com/v1/projects/%s/messages:send',

        // Default notification settings
        'default_sound' => 'default',
        'default_icon' => 'ic_notification',
        'default_color' => '#FF5722',

        // Android specific
        'android' => [
            'priority' => 'high',
            'ttl' => '86400s', // 24 hours
        ],

        // iOS/APNs specific
        'apns' => [
            'headers' => [
                'apns-priority' => '10',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy FCM HTTP API (Deprecated - for backward compatibility)
    |--------------------------------------------------------------------------
    */
    'legacy' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'endpoint' => 'https://fcm.googleapis.com/fcm/send',
    ],
];
