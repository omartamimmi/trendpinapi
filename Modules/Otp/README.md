# OTP Module

One-Time Password verification for TrendPin.

## Overview

The OTP module handles phone verification using SMS-based one-time passwords via Twilio.

## Architecture

```
Otp/
├── app/
│   └── Http/
│       └── Controllers/
│           └── OtpController.php
├── Models/
│   └── PhoneVerification.php
├── Services/
│   └── OtpService.php
├── Requests/
│   ├── SendOtpRequest.php
│   └── VerifyOtpRequest.php
└── routes/
    ├── api.php
    └── web.php
```

## Model

### PhoneVerification

**Fields:**
- `phone` - Phone number
- `otp` - Generated OTP code
- `attempts` - Verification attempts
- `expires_at` - OTP expiration time
- `verified_at` - Verification timestamp

## API Endpoints

### Send OTP
```
POST /api/v1/otp/send
{
    "phone": "+962791234567"
}
```

### Verify OTP
```
POST /api/v1/otp/verify
{
    "phone": "+962791234567",
    "otp": "123456"
}
```

## Service Layer

### OtpService

**Methods:**
- `sendOtp($phone)` - Generate and send OTP
- `verifyOtp($phone, $otp)` - Verify OTP code
- `invalidateOtp($phone)` - Invalidate existing OTPs

```php
$otpService->sendOtp('+962791234567');
$result = $otpService->verifyOtp('+962791234567', '123456');
```

## Configuration

### Twilio Setup

Add to `.env`:
```env
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1234567890
```

### Development Mode

In development, OTPs are logged instead of sent:
```php
if (app()->environment('local')) {
    Log::info("OTP for {$phone}: {$otp}");
    return true;
}
```

## Security Features

- OTP expiration (default: 10 minutes)
- Attempt limiting (max 5 attempts)
- Rate limiting on send endpoint
- Phone number validation

## Usage in Onboarding

The OTP module is used by RetailerOnboarding for:
1. Phone number verification
2. CliQ alias verification

```php
// In OnboardingService
$otpService->sendOtp($phone);
// ...later...
if ($otpService->verifyOtp($phone, $userOtp)) {
    $onboarding->phone_verified = true;
}
```

## Dependencies

- `Twilio SDK` - SMS delivery
- Used by `Modules\RetailerOnboarding`
- Used by `Modules\User` for authentication
