<?php

return [
    'name' => 'Payment',

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'tap'),

    /*
    |--------------------------------------------------------------------------
    | QR Session Settings
    |--------------------------------------------------------------------------
    */
    'qr_expiry_minutes' => env('PAYMENT_QR_EXPIRY_MINUTES', 15),
    'qr_size' => env('PAYMENT_QR_SIZE', 300),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'tap' => [
            'enabled' => env('TAP_PAYMENTS_ENABLED', false),
            'sandbox' => env('TAP_PAYMENTS_SANDBOX', true),
            'public_key' => env('TAP_PAYMENTS_PUBLIC_KEY', ''),
            'secret_key' => env('TAP_PAYMENTS_SECRET_KEY', ''),
            'webhook_secret' => env('TAP_PAYMENTS_WEBHOOK_SECRET', ''),
            'merchant_id' => env('TAP_PAYMENTS_MERCHANT_ID', ''),
            'base_url' => env('TAP_PAYMENTS_SANDBOX', true)
                ? 'https://api.tap.company/v2/'
                : 'https://api.tap.company/v2/',
            'supports' => ['card', 'apple_pay', 'google_pay'],
        ],

        'hyperpay' => [
            'enabled' => env('HYPERPAY_ENABLED', false),
            'sandbox' => env('HYPERPAY_SANDBOX', true),
            'entity_id' => env('HYPERPAY_ENTITY_ID', ''),
            'access_token' => env('HYPERPAY_ACCESS_TOKEN', ''),
            'webhook_secret' => env('HYPERPAY_WEBHOOK_SECRET', ''),
            'base_url' => env('HYPERPAY_SANDBOX', true)
                ? 'https://eu-test.oppwa.com/v1/'
                : 'https://eu-prod.oppwa.com/v1/',
            'supports' => ['card', 'apple_pay', 'google_pay'],
        ],

        'paytabs' => [
            'enabled' => env('PAYTABS_ENABLED', false),
            'sandbox' => env('PAYTABS_SANDBOX', true),
            'profile_id' => env('PAYTABS_PROFILE_ID', ''),
            'server_key' => env('PAYTABS_SERVER_KEY', ''),
            'region' => env('PAYTABS_REGION', 'JOR'),
            'webhook_secret' => env('PAYTABS_WEBHOOK_SECRET', ''),
            'base_url' => 'https://secure-jordan.paytabs.com/',
            'supports' => ['card', 'apple_pay'],
        ],

        'cliq' => [
            'enabled' => env('CLIQ_ENABLED', false),
            'merchant_alias' => env('CLIQ_MERCHANT_ALIAS', ''),
            'merchant_name' => env('CLIQ_MERCHANT_NAME', 'TrendPin'),
            'webhook_secret' => env('CLIQ_WEBHOOK_SECRET', ''),
            'jopacc_endpoint' => env('CLIQ_JOPACC_ENDPOINT', ''),
            'supports' => ['cliq'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'default' => 'JOD',
        'supported' => ['JOD', 'USD'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    */
    'methods' => [
        'card' => [
            'name' => 'Credit/Debit Card',
            'name_ar' => 'بطاقة ائتمان/خصم',
            'icon' => 'credit-card',
            'enabled' => true,
        ],
        'apple_pay' => [
            'name' => 'Apple Pay',
            'name_ar' => 'Apple Pay',
            'icon' => 'apple',
            'enabled' => true,
        ],
        'google_pay' => [
            'name' => 'Google Pay',
            'name_ar' => 'Google Pay',
            'icon' => 'google',
            'enabled' => true,
        ],
        'cliq' => [
            'name' => 'CliQ',
            'name_ar' => 'كليك',
            'icon' => 'bank',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Fees (for analytics)
    |--------------------------------------------------------------------------
    */
    'fees' => [
        'card' => 2.5, // percentage
        'apple_pay' => 2.5,
        'google_pay' => 2.5,
        'cliq' => 0.5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook URLs
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'tap' => '/webhooks/payment/tap',
        'hyperpay' => '/webhooks/payment/hyperpay',
        'paytabs' => '/webhooks/payment/paytabs',
        'cliq' => '/webhooks/payment/cliq',
    ],
];
