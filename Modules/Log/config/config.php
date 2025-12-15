<?php

return [
    'name' => 'Log',

    /*
    |--------------------------------------------------------------------------
    | Log Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep logs before automatic cleanup.
    |
    */
    'retention_days' => env('LOG_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Enabled Channels
    |--------------------------------------------------------------------------
    |
    | Channels that should be logged to database.
    |
    */
    'enabled_channels' => [
        'application',
        'auth',
        'api',
        'queue',
        'database',
        'security',
        'payment',
        'notification',
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Log Level
    |--------------------------------------------------------------------------
    |
    | Minimum level of logs to store in database.
    | Options: debug, info, notice, warning, error, critical, alert, emergency
    |
    */
    'minimum_level' => env('LOG_DB_MINIMUM_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Performance Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log performance metrics (duration, memory usage).
    |
    */
    'log_performance' => env('LOG_PERFORMANCE', true),

    /*
    |--------------------------------------------------------------------------
    | Request Logging
    |--------------------------------------------------------------------------
    |
    | Whether to log request details (URL, method, IP, user agent).
    |
    */
    'log_requests' => env('LOG_REQUESTS', true),

    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Redaction
    |--------------------------------------------------------------------------
    |
    | Keys to redact from logged context data.
    |
    */
    'redact_keys' => [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'api_key',
        'credit_card',
        'cvv',
        'authorization',
    ],
];
