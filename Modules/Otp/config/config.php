<?php

return [
    'name' => 'Otp',

    // Twilio configuration
    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_PHONE_NUMBER'),
    ],

    // OTP settings
    'code_length' => 6,
    'expiry_minutes' => 10,
    'max_attempts' => 5,
    'verification_valid_minutes' => 60,

    // SMS message template
    'message' => 'Your Trendpin verification code is: :code',

    // Rate limiting
    'rate_limit' => [
        'max_attempts' => 3,
        'decay_minutes' => 1,
    ],
];
