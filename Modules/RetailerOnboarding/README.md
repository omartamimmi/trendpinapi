# RetailerOnboarding Module

Multi-step retailer onboarding and subscription management.

## Overview

This module handles the complete retailer onboarding process including:
- Retailer details collection
- Phone verification
- Payment method setup (CliQ)
- Brand information
- Subscription selection and payment

## Architecture

```
RetailerOnboarding/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── OnboardingController.php      - API endpoints
│   │   │   ├── RetailerPageController.php    - Inertia pages
│   │   │   └── admin/
│   │   │       ├── RetailerOnboardingController.php
│   │   │       └── SubscriptionPlanController.php
│   │   └── Middleware/
│   │       └── EnsureOnboardingCompleted.php
│   └── Models/
│       ├── RetailerOnboarding.php    - Onboarding state
│       ├── RetailerPaymentMethod.php - Payment methods
│       ├── RetailerSubscription.php  - Active subscriptions
│       ├── SubscriptionPlan.php      - Available plans
│       ├── SubscriptionPayment.php   - Payment records
│       └── Offer.php                 - Special offers
├── Services/
│   └── OnboardingService.php         - Core business logic
└── routes/
    ├── api.php
    └── web.php
```

## Onboarding Steps

1. **Retailer Details** (`retailer_details`)
   - Business name, type
   - Contact information
   - License upload

2. **Payment Details** (`payment_details`)
   - CliQ alias/IBAN
   - Bank information
   - Phone verification

3. **Brand Information** (`brand_information`)
   - Brand name and description
   - Logo and gallery
   - Categories and tags

4. **Subscription** (`subscription`)
   - Plan selection
   - Pricing review

5. **Payment** (`payment`)
   - Payment confirmation
   - Receipt upload

## Models

### RetailerOnboarding
Tracks onboarding state and collected data.

**Key Fields:**
- `user_id` - Owner
- `current_step` - Current step name
- `status` - in_progress, completed
- `approval_status` - pending, pending_approval, approved, changes_requested, rejected
- `completed_steps` - Array of completed steps
- `phone_verified`, `cliq_verified` - Verification flags

### SubscriptionPlan
Available subscription plans.

**Fields:**
- `name`, `type` - Plan identification
- `price`, `offers_count` - Pricing
- `duration_months`, `billing_period`
- `is_active` - Availability flag

### RetailerSubscription
Active retailer subscriptions.

**Fields:**
- `user_id`, `subscription_plan_id`
- `status` - pending, active, cancelled, expired
- `starts_at`, `ends_at`

## API Endpoints

### Onboarding Flow
- `GET /api/v1/onboarding/current` - Get current state
- `POST /api/v1/onboarding/step/{step}` - Submit step data
- `POST /api/v1/onboarding/verify-phone` - Verify phone
- `POST /api/v1/onboarding/verify-cliq` - Verify CliQ

### Retailer Pages (Web)
- `GET /retailer/onboarding` - Onboarding wizard
- `GET /retailer/dashboard` - Retailer dashboard
- `GET /retailer/brands` - Manage brands
- `GET /retailer/brands/create` - Create brand
- `GET /retailer/brands/{id}/edit` - Edit brand

## Middleware

`EnsureOnboardingCompleted` - Blocks access to retailer features until onboarding is complete.

## Service Layer

`OnboardingService` handles all onboarding business logic:

```php
$service
    ->setInputs($data)
    ->setUser($user)
    ->processStep($step)
    ->collectOutput('onboarding', $result);
```

## Approval Workflow

1. Retailer completes onboarding
2. Status changes to `pending_approval`
3. Admin reviews in Admin panel
4. Admin can: Approve, Request Changes, or Reject
5. If approved, subscription activates

## Dependencies

- `Modules\Business` - Brand model
- `Modules\Otp` - Phone verification
- `Modules\Notification` - Status notifications
