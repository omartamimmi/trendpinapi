<?php

return [
    'name' => 'Geofence',

    /*
    |--------------------------------------------------------------------------
    | Radar.io Configuration
    |--------------------------------------------------------------------------
    */
    'radar' => [
        'secret_key' => env('RADAR_SECRET_KEY'),
        'publishable_key' => env('RADAR_PUBLISHABLE_KEY'),
        'webhook_secret' => env('RADAR_WEBHOOK_SECRET'),
        'api_url' => env('RADAR_API_URL', 'https://api.radar.io/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Geofence Default Settings
    |--------------------------------------------------------------------------
    */
    'geofence' => [
        'default_radius' => env('GEOFENCE_DEFAULT_RADIUS', 100), // meters
        'min_radius' => 50,
        'max_radius' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Throttling Rules
    |--------------------------------------------------------------------------
    | These rules prevent notification fatigue for users
    */
    'throttle' => [
        // Maximum notifications per day per user
        'max_per_day' => env('GEOFENCE_MAX_NOTIFICATIONS_PER_DAY', 5),

        // Maximum notifications per week per user
        'max_per_week' => env('GEOFENCE_MAX_NOTIFICATIONS_PER_WEEK', 15),

        // Minimum time between any notifications (minutes)
        'min_interval_minutes' => env('GEOFENCE_MIN_INTERVAL_MINUTES', 30),

        // Cooldown period for same brand (hours)
        'brand_cooldown_hours' => env('GEOFENCE_BRAND_COOLDOWN_HOURS', 24),

        // Cooldown period for same location/branch (hours)
        'location_cooldown_hours' => env('GEOFENCE_LOCATION_COOLDOWN_HOURS', 4),

        // Cooldown period for same offer (hours)
        'offer_cooldown_hours' => env('GEOFENCE_OFFER_COOLDOWN_HOURS', 48),

        // Quiet hours (don't send notifications during these hours)
        'quiet_hours' => [
            'enabled' => env('GEOFENCE_QUIET_HOURS_ENABLED', true),
            'start' => env('GEOFENCE_QUIET_HOURS_START', '22:00'),
            'end' => env('GEOFENCE_QUIET_HOURS_END', '08:00'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Interest Matching
    |--------------------------------------------------------------------------
    */
    'interest_matching' => [
        // Only send notifications if user has matching interests
        'require_interest_match' => env('GEOFENCE_REQUIRE_INTEREST_MATCH', true),

        // If false, send to all users regardless of interests
        'fallback_to_all' => env('GEOFENCE_FALLBACK_TO_ALL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notification' => [
        // Queue name for processing notifications
        'queue' => env('GEOFENCE_NOTIFICATION_QUEUE', 'geofence-notifications'),

        // Default notification channel
        'default_channel' => 'push',

        // Include offer details in notification
        'include_offer_details' => true,
    ],
];
