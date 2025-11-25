# Trendpin Payment Integration Guide

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Payment Methods](#payment-methods)
4. [Implementation Steps](#implementation-steps)
5. [Testing](#testing)
6. [API Documentation](#api-documentation)
7. [Security Considerations](#security-considerations)

---

## Overview

This guide covers the implementation of **Option C: Hybrid Payment System** for Trendpin, which includes:

1. **CliQ** - Jordan's instant payment system (QR Code payments)
2. **JoMoPay** - Jordan's mobile payment platform (NFC tap-to-pay)
3. **Stripe** - International gateway for Apple Pay & Google Pay

### Business Flow

```
Customer → Selects Payment Method → Payment Gateway → Bank → Merchant
```

**Key Features:**
- No card storage (PCI-DSS compliant)
- Real-time payment processing
- Multi-method support
- Automatic reconciliation
- Refund support

---

## Architecture

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Trendpin Platform                         │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Customer   │  │   Retailer   │  │    Admin     │      │
│  │     App      │  │   Dashboard  │  │   Dashboard  │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                  │                  │               │
│         └──────────────────┴──────────────────┘               │
│                            │                                  │
│         ┌──────────────────┴──────────────────┐              │
│         │   Payment Controller Layer           │              │
│         └──────────────────┬──────────────────┘              │
│                            │                                  │
│         ┌──────────────────┴──────────────────┐              │
│         │   Payment Service Layer              │              │
│         │  (Strategy Pattern)                  │              │
│         └──────────────────┬──────────────────┘              │
│                            │                                  │
│      ┌────────┬────────────┴────────────┬─────────┐          │
│      │        │                         │         │          │
│  ┌───▼───┐ ┌─▼────┐ ┌──────────┐ ┌────▼────┐ ┌──▼───┐      │
│  │ CliQ  │ │JoMo  │ │  Stripe  │ │ Apple   │ │Google│      │
│  │  QR   │ │ Pay  │ │          │ │  Pay    │ │ Pay  │      │
│  └───┬───┘ └─┬────┘ └────┬─────┘ └────┬────┘ └──┬───┘      │
└──────┼───────┼───────────┼────────────┼─────────┼──────────┘
       │       │           │            │         │
   ┌───▼───────▼─────┐ ┌───▼────────────▼─────────▼───┐
   │    JoPACC       │ │        Stripe API            │
   │  (Jordan Banks) │ │   (International Gateway)    │
   └─────────────────┘ └──────────────────────────────┘
```

### Database Schema

```sql
-- Payment transactions table
payments (
    id BIGINT PRIMARY KEY
    user_id BIGINT
    retailer_id BIGINT
    order_id BIGINT
    amount DECIMAL(10,2)
    currency VARCHAR(3) DEFAULT 'JOD'
    payment_method ENUM('cliq_qr', 'jomopay', 'apple_pay', 'google_pay', 'card')
    gateway VARCHAR(50) -- 'cliq', 'jomopay', 'stripe'
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded')
    transaction_id VARCHAR(255) UNIQUE
    gateway_transaction_id VARCHAR(255)
    gateway_response JSON
    metadata JSON
    completed_at TIMESTAMP
    created_at TIMESTAMP
    updated_at TIMESTAMP
)

-- Payment methods configuration
payment_methods (
    id BIGINT PRIMARY KEY
    name VARCHAR(100)
    code VARCHAR(50) UNIQUE -- 'cliq_qr', 'jomopay', etc.
    gateway VARCHAR(50)
    is_active BOOLEAN DEFAULT true
    config JSON -- API credentials, webhook URLs, etc.
    sort_order INT
    created_at TIMESTAMP
    updated_at TIMESTAMP
)

-- Webhook logs
payment_webhooks (
    id BIGINT PRIMARY KEY
    payment_id BIGINT
    gateway VARCHAR(50)
    event_type VARCHAR(100)
    payload JSON
    processed BOOLEAN DEFAULT false
    processed_at TIMESTAMP
    created_at TIMESTAMP
)
```

---

## Payment Methods

### 1. CliQ QR Code Payments

**Provider:** JoPACC (Jordan Payments & Clearing Company)

**How it works:**
1. Merchant generates dynamic QR code via CliQ API
2. Customer scans QR code with banking app
3. Customer authorizes payment
4. Instant settlement to merchant account

**Integration Requirements:**
- Merchant account with participating Jordanian bank
- CliQ merchant ID
- API credentials from JoPACC or acquiring bank
- Webhook endpoint for payment notifications

**API Endpoints:**
```
POST /api/payments/cliq/generate-qr
POST /api/payments/cliq/check-status
POST /api/webhooks/cliq
```

**Implementation:**
```php
// Generate QR Code
$cliqService = new CliqPaymentService();
$qrCode = $cliqService->generateQR([
    'amount' => 25.00,
    'currency' => 'JOD',
    'order_id' => 'ORD-12345',
    'merchant_id' => config('payments.cliq.merchant_id'),
    'callback_url' => route('webhooks.cliq')
]);

// Returns:
{
    "qr_code": "data:image/png;base64,iVBORw0KG...",
    "qr_data": "cliq://pay?merchant=xxx&amount=25.00&ref=xxx",
    "reference": "CLQ-12345-ABCD",
    "expires_at": "2025-11-25T15:30:00Z"
}
```

**Testing:**
- Use JoPACC sandbox environment
- Test QR generation
- Simulate payment completion via webhook

---

### 2. JoMoPay (NFC Tap-to-Pay)

**Provider:** JoPACC

**How it works:**
1. Customer has JoMoPay wallet enabled in banking app
2. Merchant displays payment amount in app
3. Customer taps NFC-enabled phone to merchant device
4. Payment processed through JoMoPay network

**Integration Requirements:**
- JoMoPay merchant account
- Jordan Open Finance Standards API access
- NFC-enabled Android/iOS app
- POS integration (optional)

**API Endpoints:**
```
POST /api/payments/jomopay/initiate
POST /api/payments/jomopay/confirm
POST /api/webhooks/jomopay
```

**Implementation:**
```php
// Initiate NFC payment
$jomopayService = new JoMoPayService();
$payment = $jomopayService->initiate([
    'amount' => 50.00,
    'currency' => 'JOD',
    'order_id' => 'ORD-12345',
    'customer_wallet_id' => '079xxxxxxx', // Optional
    'merchant_id' => config('payments.jomopay.merchant_id')
]);

// Returns:
{
    "payment_id": "JMP-12345-ABCD",
    "status": "pending",
    "nfc_data": "encrypted_nfc_payload",
    "expires_at": "2025-11-25T15:35:00Z"
}
```

**Testing:**
- Use Jordan Open Finance Sandbox
- Test with sandbox wallet credentials
- Simulate NFC tap events

---

### 3. Apple Pay & Google Pay via Stripe

**Provider:** Stripe

**How it works:**
1. Customer selects Apple Pay/Google Pay at checkout
2. Payment sheet appears on device
3. Customer authenticates (Face ID, Touch ID, PIN)
4. Tokenized payment sent to Stripe
5. Stripe processes payment

**Integration Requirements:**
- Stripe account (supports Jordan)
- Apple Developer account (for Apple Pay)
- Google Pay Business Console account
- SSL certificate (HTTPS required)

**API Endpoints:**
```
POST /api/payments/stripe/create-payment-intent
POST /api/payments/stripe/confirm
POST /api/webhooks/stripe
```

**Implementation:**
```php
// Create payment intent
$stripeService = new StripePaymentService();
$paymentIntent = $stripeService->createPaymentIntent([
    'amount' => 75.00,
    'currency' => 'jod',
    'order_id' => 'ORD-12345',
    'payment_method_types' => ['card', 'apple_pay', 'google_pay'],
    'metadata' => [
        'customer_id' => auth()->id(),
        'order_id' => 'ORD-12345'
    ]
]);

// Returns:
{
    "client_secret": "pi_xxx_secret_xxx",
    "payment_intent_id": "pi_xxx",
    "status": "requires_payment_method"
}
```

**Frontend Integration:**
```javascript
// React component for Stripe
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement } from '@stripe/react-stripe-js';

const stripePromise = loadStripe('pk_test_xxx');

function CheckoutForm({ clientSecret }) {
    const stripe = useStripe();
    const elements = useElements();

    const handleSubmit = async (e) => {
        e.preventDefault();

        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: 'https://trendpin.com/payment/success',
            },
        });

        if (error) {
            // Handle error
        } else {
            // Payment successful
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <PaymentElement />
            <button type="submit">Pay Now</button>
        </form>
    );
}
```

**Testing:**
- Use Stripe test mode
- Test cards: 4242 4242 4242 4242
- Apple Pay: Use Safari on iPhone with test account
- Google Pay: Use Chrome with test account

---

## Implementation Steps

### Step 1: Database Setup

```bash
php artisan make:migration create_payments_table
php artisan make:migration create_payment_methods_table
php artisan make:migration create_payment_webhooks_table
```

### Step 2: Install Dependencies

```bash
composer require stripe/stripe-php
composer require guzzlehttp/guzzle
```

```bash
npm install @stripe/stripe-js @stripe/react-stripe-js
```

### Step 3: Create Models

```bash
php artisan make:model Payment
php artisan make:model PaymentMethod
php artisan make:model PaymentWebhook
```

### Step 4: Create Service Layer

```bash
php artisan make:service PaymentGatewayFactory
php artisan make:service CliqPaymentService
php artisan make:service JoMoPayService
php artisan make:service StripePaymentService
```

### Step 5: Create Controllers

```bash
php artisan make:controller Api/PaymentController
php artisan make:controller Api/CliQPaymentController
php artisan make:controller Api/JoMoPaymentController
php artisan make:controller Api/StripePaymentController
php artisan make:controller WebhookController
```

### Step 6: Add Configuration

**config/payments.php:**
```php
<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'cliq'),
    'currency' => env('PAYMENT_CURRENCY', 'JOD'),

    'cliq' => [
        'enabled' => env('CLIQ_ENABLED', true),
        'merchant_id' => env('CLIQ_MERCHANT_ID'),
        'api_key' => env('CLIQ_API_KEY'),
        'api_secret' => env('CLIQ_API_SECRET'),
        'api_url' => env('CLIQ_API_URL', 'https://sandbox.cliq.jo/api'),
        'webhook_secret' => env('CLIQ_WEBHOOK_SECRET'),
    ],

    'jomopay' => [
        'enabled' => env('JOMOPAY_ENABLED', false),
        'merchant_id' => env('JOMOPAY_MERCHANT_ID'),
        'api_key' => env('JOMOPAY_API_KEY'),
        'api_url' => env('JOMOPAY_API_URL', 'https://sandbox.jomopay.jo/api'),
        'webhook_secret' => env('JOMOPAY_WEBHOOK_SECRET'),
    ],

    'stripe' => [
        'enabled' => env('STRIPE_ENABLED', true),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'apple_pay' => [
            'enabled' => env('APPLE_PAY_ENABLED', true),
            'merchant_id' => env('APPLE_PAY_MERCHANT_ID'),
        ],
        'google_pay' => [
            'enabled' => env('GOOGLE_PAY_ENABLED', true),
            'merchant_id' => env('GOOGLE_PAY_MERCHANT_ID'),
        ],
    ],
];
```

**.env additions:**
```env
# CliQ Configuration
CLIQ_ENABLED=true
CLIQ_MERCHANT_ID=your_merchant_id
CLIQ_API_KEY=your_api_key
CLIQ_API_SECRET=your_api_secret
CLIQ_API_URL=https://sandbox.cliq.jo/api
CLIQ_WEBHOOK_SECRET=your_webhook_secret

# JoMoPay Configuration
JOMOPAY_ENABLED=false
JOMOPAY_MERCHANT_ID=your_merchant_id
JOMOPAY_API_KEY=your_api_key
JOMOPAY_API_URL=https://sandbox.jomopay.jo/api
JOMOPAY_WEBHOOK_SECRET=your_webhook_secret

# Stripe Configuration
STRIPE_ENABLED=true
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
APPLE_PAY_ENABLED=true
APPLE_PAY_MERCHANT_ID=merchant.com.trendpin
GOOGLE_PAY_ENABLED=true
GOOGLE_PAY_MERCHANT_ID=your_google_merchant_id
```

### Step 7: Create Routes

**routes/api.php:**
```php
// Payment routes
Route::prefix('payments')->middleware(['auth:sanctum'])->group(function () {
    // Generic payment endpoints
    Route::post('/initiate', [PaymentController::class, 'initiate']);
    Route::get('/{id}/status', [PaymentController::class, 'status']);
    Route::post('/{id}/cancel', [PaymentController::class, 'cancel']);

    // CliQ QR Code payments
    Route::prefix('cliq')->group(function () {
        Route::post('/generate-qr', [CliQPaymentController::class, 'generateQR']);
        Route::post('/check-status', [CliQPaymentController::class, 'checkStatus']);
    });

    // JoMoPay NFC payments
    Route::prefix('jomopay')->group(function () {
        Route::post('/initiate', [JoMoPaymentController::class, 'initiate']);
        Route::post('/confirm', [JoMoPaymentController::class, 'confirm']);
    });

    // Stripe payments (Apple Pay / Google Pay)
    Route::prefix('stripe')->group(function () {
        Route::post('/payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
        Route::post('/confirm', [StripePaymentController::class, 'confirm']);
    });
});

// Webhook endpoints (no auth required)
Route::prefix('webhooks')->group(function () {
    Route::post('/cliq', [WebhookController::class, 'cliq']);
    Route::post('/jomopay', [WebhookController::class, 'jomopay']);
    Route::post('/stripe', [WebhookController::class, 'stripe']);
});
```

---

## Testing

### Setting Up Test Environment

#### 1. CliQ Testing

**Contact for Sandbox Access:**
- Email: support@jopacc.com
- Website: https://www.jopacc.com
- Request sandbox merchant account

**Test Credentials:**
```env
CLIQ_API_URL=https://sandbox.cliq.jo/api
CLIQ_MERCHANT_ID=TEST_MERCHANT_001
CLIQ_API_KEY=test_key_xxx
CLIQ_API_SECRET=test_secret_xxx
```

**Test Cases:**
1. Generate QR code
2. Scan with test banking app
3. Confirm payment
4. Receive webhook
5. Handle timeout/expiry

#### 2. JoMoPay Testing

**Access Jordan Open Finance Sandbox:**
- URL: https://sandbox.jopacc.com
- Register for developer account
- Request JoMoPay test credentials

**Test Wallet Numbers:**
```
079xxxxxxx (successful payment)
079yyyyyyy (insufficient funds)
079zzzzzzz (network timeout)
```

**Test Cases:**
1. Initiate NFC payment
2. Simulate tap event
3. Process payment
4. Handle errors
5. Test refunds

#### 3. Stripe Testing

**Stripe Test Mode:**
- Dashboard: https://dashboard.stripe.com/test
- Use test API keys from dashboard

**Test Cards:**
```
Visa: 4242 4242 4242 4242
Mastercard: 5555 5555 5555 4444
3D Secure: 4000 0027 6000 3184
Declined: 4000 0000 0000 0002
```

**Apple Pay Testing:**
1. Use Safari on iPhone/Mac
2. Add test card to Wallet
3. Domain verification required

**Google Pay Testing:**
1. Use Chrome browser
2. Add test card to Google account
3. Enable test mode

**Test Cases:**
1. Create payment intent
2. Confirm with Apple Pay
3. Confirm with Google Pay
4. Handle 3D Secure
5. Test refunds
6. Webhook handling

### Automated Testing

**PHPUnit Tests:**
```php
// tests/Feature/PaymentTest.php
public function test_can_generate_cliq_qr_code()
{
    $response = $this->postJson('/api/payments/cliq/generate-qr', [
        'amount' => 25.00,
        'order_id' => 'TEST-001'
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'qr_code',
                 'reference',
                 'expires_at'
             ]);
}

public function test_can_create_stripe_payment_intent()
{
    $response = $this->postJson('/api/payments/stripe/payment-intent', [
        'amount' => 50.00,
        'order_id' => 'TEST-002'
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'client_secret',
                 'payment_intent_id'
             ]);
}
```

**Run Tests:**
```bash
php artisan test --filter PaymentTest
```

---

## API Documentation

### CliQ QR Code API

#### Generate QR Code

**Endpoint:** `POST /api/payments/cliq/generate-qr`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "amount": 25.00,
    "currency": "JOD",
    "order_id": "ORD-12345",
    "description": "Order payment"
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "qr_code": "data:image/png;base64,iVBORw0KG...",
        "qr_data": "cliq://pay?merchant=xxx&amount=25.00",
        "reference": "CLQ-12345-ABCD",
        "payment_id": 123,
        "status": "pending",
        "expires_at": "2025-11-25T15:30:00Z"
    }
}
```

#### Check Payment Status

**Endpoint:** `POST /api/payments/cliq/check-status`

**Request Body:**
```json
{
    "reference": "CLQ-12345-ABCD"
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "payment_id": 123,
        "reference": "CLQ-12345-ABCD",
        "status": "completed",
        "amount": 25.00,
        "completed_at": "2025-11-25T15:28:34Z"
    }
}
```

---

### JoMoPay NFC API

#### Initiate Payment

**Endpoint:** `POST /api/payments/jomopay/initiate`

**Request Body:**
```json
{
    "amount": 50.00,
    "currency": "JOD",
    "order_id": "ORD-12345"
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "payment_id": 124,
        "jomopay_ref": "JMP-12345-ABCD",
        "status": "pending",
        "nfc_data": "encrypted_payload",
        "expires_at": "2025-11-25T15:35:00Z"
    }
}
```

#### Confirm Payment

**Endpoint:** `POST /api/payments/jomopay/confirm`

**Request Body:**
```json
{
    "payment_id": 124,
    "nfc_response": "encrypted_customer_response"
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "payment_id": 124,
        "status": "completed",
        "transaction_id": "TXN-ABCD-1234",
        "completed_at": "2025-11-25T15:33:21Z"
    }
}
```

---

### Stripe (Apple Pay / Google Pay) API

#### Create Payment Intent

**Endpoint:** `POST /api/payments/stripe/payment-intent`

**Request Body:**
```json
{
    "amount": 75.00,
    "currency": "jod",
    "order_id": "ORD-12345",
    "payment_method_types": ["card", "apple_pay", "google_pay"]
}
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "client_secret": "pi_xxx_secret_xxx",
        "payment_intent_id": "pi_xxx",
        "publishable_key": "pk_test_xxx",
        "amount": 75.00,
        "currency": "jod"
    }
}
```

---

## Security Considerations

### 1. PCI-DSS Compliance

**What we DO:**
- Use tokenization (Stripe handles actual card data)
- Store only last 4 digits
- Use secure payment gateways
- Implement SSL/TLS

**What we DON'T do:**
- Store full card numbers
- Store CVV
- Store card expiry dates
- Handle raw card data

### 2. Webhook Security

**Verify webhook signatures:**
```php
// CliQ webhook verification
$signature = $request->header('X-Cliq-Signature');
$payload = $request->getContent();
$expectedSignature = hash_hmac('sha256', $payload, config('payments.cliq.webhook_secret'));

if (!hash_equals($signature, $expectedSignature)) {
    abort(403, 'Invalid signature');
}

// Stripe webhook verification
$stripe = new \Stripe\StripeClient(config('payments.stripe.secret'));
try {
    $event = \Stripe\Webhook::constructEvent(
        $request->getContent(),
        $request->header('Stripe-Signature'),
        config('payments.stripe.webhook_secret')
    );
} catch (\Exception $e) {
    abort(403, 'Invalid signature');
}
```

### 3. Rate Limiting

**Protect payment endpoints:**
```php
// routes/api.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/payments/cliq/generate-qr', ...);
    Route::post('/payments/stripe/payment-intent', ...);
});
```

### 4. Idempotency

**Prevent duplicate charges:**
```php
// Add idempotency key
$payment = Payment::firstOrCreate(
    ['idempotency_key' => $request->input('idempotency_key')],
    $paymentData
);

if ($payment->wasRecentlyCreated) {
    // Process payment
} else {
    // Return existing payment
    return response()->json(['data' => $payment]);
}
```

### 5. Data Encryption

**Encrypt sensitive data:**
```php
use Illuminate\Support\Facades\Crypt;

// Store encrypted gateway response
$payment->gateway_response = Crypt::encrypt($gatewayResponse);

// Decrypt when needed
$response = Crypt::decrypt($payment->gateway_response);
```

---

## Monitoring & Logging

### Payment Logging

```php
Log::channel('payments')->info('Payment initiated', [
    'payment_id' => $payment->id,
    'amount' => $payment->amount,
    'gateway' => $payment->gateway,
    'user_id' => $payment->user_id
]);

Log::channel('payments')->info('Payment completed', [
    'payment_id' => $payment->id,
    'transaction_id' => $payment->transaction_id,
    'duration_ms' => $duration
]);

Log::channel('payments')->error('Payment failed', [
    'payment_id' => $payment->id,
    'error' => $exception->getMessage(),
    'gateway_response' => $gatewayResponse
]);
```

### Webhook Logging

```php
PaymentWebhook::create([
    'payment_id' => $payment->id,
    'gateway' => 'stripe',
    'event_type' => $event->type,
    'payload' => $event->data,
    'processed' => true,
    'processed_at' => now()
]);
```

---

## Next Steps

### Phase 1: CliQ Integration (Week 1-2)
1. Set up JoPACC sandbox account
2. Implement QR code generation
3. Test with sandbox
4. Deploy to production

### Phase 2: Stripe Integration (Week 2-3)
1. Create Stripe account
2. Implement Apple Pay
3. Implement Google Pay
4. Test with test cards

### Phase 3: JoMoPay Integration (Week 4-5)
1. Apply for Jordan Open Finance access
2. Implement NFC payment flow
3. Test with sandbox
4. Deploy to production

### Phase 4: Testing & Optimization (Week 6)
1. End-to-end testing
2. Performance optimization
3. Security audit
4. Documentation finalization

---

## Support & Resources

### Official Documentation
- **CliQ:** https://www.jopacc.com/cliq-system
- **JoMoPay:** https://www.jopacc.com/jomopay
- **Stripe:** https://stripe.com/docs/api
- **Apple Pay:** https://developer.apple.com/apple-pay/
- **Google Pay:** https://developers.google.com/pay

### Contact Information
- **JoPACC Support:** support@jopacc.com
- **Stripe Support:** https://support.stripe.com
- **Trendpin Technical Team:** tech@trendpin.com

---

**Document Version:** 1.0
**Last Updated:** November 25, 2025
**Author:** Trendpin Development Team
