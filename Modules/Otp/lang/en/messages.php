<?php

return [
    // Success messages
    'code_sent' => 'Verification code has been sent to your phone.',
    'verification_success' => 'Phone number verified successfully.',

    // Error messages
    'verification_not_found' => 'No verification request found for this phone number.',
    'code_expired' => 'Verification code has expired. Please request a new one.',
    'max_attempts_exceeded' => 'Maximum verification attempts exceeded. Please request a new code.',
    'invalid_code' => 'Invalid verification code.',
    'sms_send_failed' => 'Failed to send verification code',

    // Validation messages
    'phone_required' => 'Phone number is required.',
    'phone_invalid' => 'Please enter a valid phone number.',
    'code_required' => 'Verification code is required.',
    'code_invalid_length' => 'Verification code must be :size digits.',
];
