# TrendPin Payment System Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [API Reference](#api-reference)
6. [Admin Dashboard](#admin-dashboard)
7. [WebSocket Events](#websocket-events)
8. [React Native Integration](#react-native-integration)
9. [Testing](#testing)

---

## Overview

The TrendPin Payment System is a QR-based payment solution designed for two mobile applications:

1. **TrendPin Retailer App** - Retailers enter amounts and generate QR codes
2. **TrendPin Customer App** - Customers scan QR codes and pay with automatic bank discounts

### Key Features

- **Multi-gateway support**: Tap Payments, HyperPay, PayTabs, CliQ
- **Automatic bank discount**: Detects card BIN and applies available offers
- **One-tap payments**: Saved card tokenization for instant payments
- **Wallet payments**: Apple Pay and Google Pay support
- **CliQ integration**: Jordan's instant bank transfer system
- **Real-time updates**: WebSocket notifications for payment status
- **Subscription validation**: Only subscribed retailers can accept payments
- **Comprehensive analytics**: Transaction reports, conversion tracking, bank offer performance

### Payment Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      RETAILER APP                                │
│  1. Enter amount → 2. Generate QR → 3. Wait for payment         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Customer scans QR
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CUSTOMER APP                                │
│  1. Scan QR → 2. See discount → 3. Pay → 4. Get receipt         │
└─────────────────────────────────────────────────────────────────┘
```

---

## Architecture

### Module Structure

```
Modules/Payment/
├── app/
│   ├── DTO/
│   │   ├── PaymentRequestDTO.php
│   │   └── PaymentResponseDTO.php
│   ├── Enums/
│   │   ├── PaymentMethod.php
│   │   └── PaymentStatus.php
│   ├── Events/
│   │   ├── QrSessionScanned.php
│   │   ├── QrSessionProcessing.php
│   │   ├── QrSessionCompleted.php
│   │   ├── QrSessionExpired.php
│   │   ├── QrSessionCancelled.php
│   │   ├── PaymentCompleted.php
│   │   └── PaymentFailed.php
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── RetailerQrSessionController.php
│   │   │   └── CustomerPaymentController.php
│   │   ├── Admin/
│   │   │   ├── PaymentSettingsController.php
│   │   │   └── PaymentAnalyticsController.php
│   │   └── Webhook/
│   │       ├── TapPaymentsWebhookController.php
│   │       └── CliqWebhookController.php
│   └── Models/
│       ├── PaymentSetting.php
│       ├── PaymentMethodSetting.php
│       ├── PaymentTransaction.php
│       ├── QrPaymentSession.php
│       ├── TokenizedCard.php
│       └── CliqPaymentRequest.php
├── Services/
│   ├── Contracts/
│   │   └── PaymentGatewayInterface.php
│   ├── Gateways/
│   │   ├── BaseGateway.php
│   │   └── TapPaymentsGateway.php
│   ├── BankDiscountService.php
│   └── PaymentService.php
├── config/config.php
├── database/migrations/
└── routes/api.php
```

### Database Schema

#### `qr_payment_sessions`
Stores QR payment sessions created by retailers.

| Column | Type | Description |
|--------|------|-------------|
| session_code | VARCHAR(20) | Unique session identifier (e.g., TRP-ABC123XYZ) |
| qr_code_data | TEXT | QR code URL/data |
| qr_code_image | TEXT | Base64 encoded QR image |
| retailer_id | BIGINT | Retailer who created the session |
| branch_id | BIGINT | Branch location |
| amount | DECIMAL(10,2) | Payment amount |
| discount_amount | DECIMAL(10,2) | Applied discount |
| final_amount | DECIMAL(10,2) | Amount after discount |
| status | ENUM | pending, scanned, processing, completed, expired, cancelled |
| expires_at | TIMESTAMP | Session expiry time |

#### `payment_transactions`
Stores all payment transactions.

| Column | Type | Description |
|--------|------|-------------|
| reference | VARCHAR(50) | Unique payment reference |
| gateway_transaction_id | VARCHAR(255) | Gateway's transaction ID |
| gateway | VARCHAR(50) | Payment gateway used |
| payment_method | VARCHAR(50) | card, apple_pay, google_pay, cliq |
| amount | DECIMAL(10,2) | Final charged amount |
| original_amount | DECIMAL(10,2) | Amount before discount |
| discount_amount | DECIMAL(10,2) | Discount applied |
| status | VARCHAR(20) | Transaction status |
| customer_id | BIGINT | Customer who paid |
| bank_offer_id | BIGINT | Applied bank offer |

#### `tokenized_cards`
Stores customer saved cards.

| Column | Type | Description |
|--------|------|-------------|
| gateway_token | VARCHAR(255) | Token from payment gateway |
| gateway_customer_id | VARCHAR(255) | Customer ID at gateway |
| card_last_four | VARCHAR(4) | Last 4 digits |
| card_brand | VARCHAR(20) | visa, mastercard, etc. |
| bin_prefix | VARCHAR(8) | First 6-8 digits for bank detection |
| bank_id | BIGINT | Detected bank |

---

## Installation

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=payment-config
```

### 3. Install QR Code Package

```bash
composer require simplesoftwareio/simple-qrcode
```

### 4. Configure Broadcasting (for WebSocket)

```bash
# In .env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

---

## Configuration

### Environment Variables

```env
# Default Gateway
PAYMENT_DEFAULT_GATEWAY=tap

# QR Session Settings
PAYMENT_QR_EXPIRY_MINUTES=15

# Tap Payments
TAP_PAYMENTS_ENABLED=true
TAP_PAYMENTS_SANDBOX=true
TAP_PAYMENTS_PUBLIC_KEY=pk_test_xxx
TAP_PAYMENTS_SECRET_KEY=sk_test_xxx
TAP_PAYMENTS_WEBHOOK_SECRET=whsec_xxx

# HyperPay (optional)
HYPERPAY_ENABLED=false
HYPERPAY_SANDBOX=true
HYPERPAY_ENTITY_ID=xxx
HYPERPAY_ACCESS_TOKEN=xxx

# CliQ
CLIQ_ENABLED=true
CLIQ_MERCHANT_ALIAS=TRENDPIN_MERCHANT
CLIQ_MERCHANT_ID=xxx
CLIQ_API_KEY=xxx
```

### Admin Dashboard Configuration

Payment settings can be managed via the Admin API:

```bash
# Enable/disable payment method
PUT /api/v1/admin/payment/methods/card
{
    "is_enabled": true,
    "fee_type": "percentage",
    "fee_value": 2.5
}

# Configure gateway credentials
PUT /api/v1/admin/payment/gateways/tap
{
    "is_enabled": true,
    "is_sandbox": false,
    "credentials": {
        "public_key": "pk_live_xxx",
        "secret_key": "sk_live_xxx"
    }
}
```

---

## API Reference

### Retailer App APIs

#### Create QR Session
```http
POST /api/v1/retailer/qr-sessions
Authorization: Bearer {token}

{
    "amount": 50.00,
    "description": "Purchase at Nike Store"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_code": "TRP-ABC123XYZ",
        "qr_code_image": "data:image/png;base64,...",
        "qr_code_data": "https://pay.trendpin.app/s/TRP-ABC123XYZ",
        "amount": 50.00,
        "currency": "JOD",
        "retailer": {
            "name": "Nike",
            "branch": "City Mall"
        },
        "status": "pending",
        "expires_at": "2025-12-22T11:00:00Z",
        "expires_in_seconds": 900
    }
}
```

#### Get Session Status (Polling)
```http
GET /api/v1/retailer/qr-sessions/{code}/status
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_code": "TRP-ABC123XYZ",
        "status": "completed",
        "amount": 50.00,
        "final_amount": 45.00,
        "discount_amount": 5.00,
        "customer": {
            "name": "A***",
            "phone_last_four": "1234"
        },
        "payment": {
            "id": 456,
            "transaction_id": "chg_xxx",
            "card_last_four": "4242",
            "card_brand": "visa"
        },
        "completed_at": "2025-12-22T10:50:00Z"
    }
}
```

#### Cancel Session
```http
POST /api/v1/retailer/qr-sessions/{code}/cancel
Authorization: Bearer {token}
```

### Customer App APIs

#### Scan QR Code
```http
POST /api/v1/customer/qr-sessions/{code}/scan
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_code": "TRP-ABC123XYZ",
        "amount": 50.00,
        "currency": "JOD",
        "retailer": {
            "name": "Nike",
            "logo": "https://..."
        },
        "branch": {
            "name": "City Mall",
            "location": "Amman, Jordan"
        },
        "available_offers": [
            {
                "bank_id": 3,
                "bank_name": "Arab Bank",
                "bank_logo": "https://...",
                "offer_display": "10% Off",
                "potential_savings": 5.00
            }
        ],
        "saved_cards": [
            {
                "id": 5,
                "last_four": "4242",
                "brand": "visa",
                "bank": {
                    "name": "Arab Bank",
                    "logo": "https://..."
                },
                "has_active_offer": true,
                "potential_savings": 5.00
            }
        ],
        "enabled_payment_methods": {
            "card": true,
            "apple_pay": true,
            "google_pay": true,
            "cliq": true
        }
    }
}
```

#### Calculate Discount
```http
POST /api/v1/customer/qr-sessions/{code}/calculate-discount
Authorization: Bearer {token}

{
    "card_bin": "411111"
}
```
or
```json
{
    "tokenized_card_id": 5
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "has_discount": true,
        "original_amount": 50.00,
        "discount_amount": 5.00,
        "final_amount": 45.00,
        "bank": {
            "id": 3,
            "name": "Arab Bank",
            "logo": "https://..."
        },
        "offer": {
            "title": "10% Off at All Partners",
            "display": "10% Off"
        },
        "message": "You'll save JOD 5.00 with Arab Bank!"
    }
}
```

#### Pay with Saved Card (One-Tap)
```http
POST /api/v1/customer/qr-sessions/{code}/pay-with-saved-card
Authorization: Bearer {token}

{
    "tokenized_card_id": 5
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_code": "TRP-ABC123XYZ",
        "payment_id": 456,
        "status": "completed",
        "original_amount": 50.00,
        "discount_amount": 5.00,
        "final_amount": 45.00,
        "transaction_id": "chg_xxx",
        "receipt": {
            "retailer": "Nike - City Mall",
            "date": "2025-12-22 10:50:00",
            "amount_paid": 45.00,
            "discount_applied": 5.00,
            "bank_offer": "10% Off with Arab Bank",
            "card": "**** 4242"
        }
    }
}
```

#### Pay with New Card (3DS)
```http
POST /api/v1/customer/qr-sessions/{code}/pay
Authorization: Bearer {token}

{
    "gateway": "tap",
    "card_bin": "411111",
    "save_card": true,
    "redirect_url": "trendpin://payment/callback"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "session_code": "TRP-ABC123XYZ",
        "payment_id": 456,
        "status": "processing",
        "redirect_url": "https://checkout.tap.company/v2/checkout?id=chg_xxx",
        "requires_redirect": true
    }
}
```

#### Pay with Apple Pay / Google Pay
```http
POST /api/v1/customer/qr-sessions/{code}/pay-with-wallet
Authorization: Bearer {token}

{
    "wallet_type": "apple_pay",
    "payment_token": "eyJhbGciOiJS..."
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": "completed",
        "original_amount": 50.00,
        "authorized_amount": 50.00,
        "discount_amount": 5.00,
        "final_amount": 45.00,
        "captured_amount": 45.00,
        "message": "You saved JOD 5.00 with Arab Bank!"
    }
}
```

#### Pay with CliQ
```http
POST /api/v1/customer/qr-sessions/{code}/pay-with-cliq
Authorization: Bearer {token}

{
    "bank_id": 5
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": "pending_bank_confirmation",
        "original_amount": 50.00,
        "discount_amount": 7.50,
        "final_amount": 42.50,
        "bank": {
            "name": "Jordan Islamic Bank"
        },
        "deep_link": "jib://pay?amount=42.50&ref=CLIQ-xxx",
        "universal_link": "https://pay.trendpin.app/cliq/xxx",
        "instructions": "Complete payment in your Jordan Islamic Bank app",
        "expires_at": "2025-12-22T11:00:00Z"
    }
}
```

### Saved Cards APIs

#### List Saved Cards
```http
GET /api/v1/customer/cards
Authorization: Bearer {token}
```

#### Save New Card
```http
POST /api/v1/customer/cards
Authorization: Bearer {token}

{
    "card_number": "4111111111111111",
    "exp_month": "12",
    "exp_year": "2027",
    "cvv": "123",
    "cardholder_name": "Ahmad Mohammed",
    "nickname": "My Visa Card"
}
```

#### Delete Card
```http
DELETE /api/v1/customer/cards/{id}
Authorization: Bearer {token}
```

---

## Admin Dashboard

### Payment Settings

#### Get Settings Overview
```http
GET /api/v1/admin/payment/settings
Authorization: Bearer {admin_token}
```

#### List Gateways
```http
GET /api/v1/admin/payment/gateways
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "gateway": "tap",
            "display_name": "Tap Payments",
            "is_enabled": true,
            "is_sandbox": false,
            "supported_methods": ["card", "apple_pay", "google_pay"],
            "is_default": true,
            "has_credentials": true,
            "test_status": "success",
            "credential_fields": [
                {"key": "public_key", "label": "Public Key", "type": "text"},
                {"key": "secret_key", "label": "Secret Key", "type": "password"}
            ]
        }
    ]
}
```

#### Update Gateway
```http
PUT /api/v1/admin/payment/gateways/tap
Authorization: Bearer {admin_token}

{
    "is_enabled": true,
    "is_sandbox": false,
    "credentials": {
        "public_key": "pk_live_xxx",
        "secret_key": "sk_live_xxx"
    }
}
```

#### Test Gateway Connection
```http
POST /api/v1/admin/payment/gateways/tap/test
Authorization: Bearer {admin_token}
```

#### Toggle Payment Method
```http
POST /api/v1/admin/payment/methods/card/toggle
Authorization: Bearer {admin_token}
```

### Analytics

#### Dashboard Overview
```http
GET /api/v1/admin/payment/analytics/dashboard?date_from=2025-12-01&date_to=2025-12-22
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "payments": {
            "total_transactions": 1250,
            "total_amount": 45000.00,
            "total_discount": 3500.00,
            "average_transaction": 36.00,
            "transaction_count_change": 15.5,
            "amount_change": 22.3
        },
        "sessions": {
            "total_created": 1500,
            "completed": 1250,
            "expired": 200,
            "conversion_rate": 83.33
        },
        "discounts": {
            "total_redemptions": 800,
            "total_discount_given": 3500.00,
            "average_discount": 4.38
        }
    }
}
```

#### Transactions List
```http
GET /api/v1/admin/payment/analytics/transactions?status=completed&per_page=50
Authorization: Bearer {admin_token}
```

#### Analytics by Gateway
```http
GET /api/v1/admin/payment/analytics/by-gateway
Authorization: Bearer {admin_token}
```

#### Analytics by Bank
```http
GET /api/v1/admin/payment/analytics/by-bank
Authorization: Bearer {admin_token}
```

#### Payment Trends
```http
GET /api/v1/admin/payment/analytics/trends?period=daily
Authorization: Bearer {admin_token}
```

#### Conversion Analytics
```http
GET /api/v1/admin/payment/analytics/conversion
Authorization: Bearer {admin_token}
```

---

## WebSocket Events

### Channel Structure

```javascript
// Retailer channel
private-retailer.{retailer_id}.qr-sessions

// Customer channel
private-customer.{user_id}.payments
```

### Events

#### session.scanned
Received by retailer when customer scans QR.

```javascript
{
    "session_code": "TRP-ABC123XYZ",
    "customer": {
        "name": "A***",
        "avatar": null
    },
    "scanned_at": "2025-12-22T10:47:00Z",
    "amount": 50.00
}
```

#### session.processing
Received by retailer when payment starts processing.

```javascript
{
    "session_code": "TRP-ABC123XYZ",
    "original_amount": 50.00,
    "discount_amount": 5.00,
    "final_amount": 45.00,
    "payment_method": "card"
}
```

#### session.completed
Received by retailer and customer when payment completes.

```javascript
{
    "session_code": "TRP-ABC123XYZ",
    "payment_id": 456,
    "transaction_id": "chg_xxx",
    "original_amount": 50.00,
    "discount_amount": 5.00,
    "final_amount": 45.00,
    "customer": {
        "name": "Ahmad M.",
        "phone_last_four": "1234"
    },
    "card": {
        "last_four": "4242",
        "brand": "visa"
    },
    "bank_offer": {
        "bank_name": "Arab Bank",
        "offer_display": "10% Off"
    },
    "completed_at": "2025-12-22T10:50:00Z"
}
```

#### session.expired
Received when session expires.

```javascript
{
    "session_code": "TRP-ABC123XYZ",
    "amount": 50.00,
    "expired_at": "2025-12-22T11:00:00Z"
}
```

#### session.cancelled
Received when retailer cancels session.

```javascript
{
    "session_code": "TRP-ABC123XYZ",
    "amount": 50.00,
    "cancelled_at": "2025-12-22T10:55:00Z"
}
```

---

## React Native Integration

### Installation

```bash
# Install required packages
npm install @pusher/pusher-websocket-react-native
npm install react-native-qrcode-scanner
npm install react-native-camera
npm install axios
```

### API Service

```typescript
// src/services/api.ts
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://api.trendpin.app/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### Payment Service

```typescript
// src/services/paymentService.ts
import api from './api';

export interface QrSession {
  session_code: string;
  qr_code_image: string;
  amount: number;
  currency: string;
  status: string;
  expires_at: string;
  retailer: {
    name: string;
    logo?: string;
  };
  branch: {
    name: string;
    location?: string;
  };
}

export interface DiscountResult {
  has_discount: boolean;
  original_amount: number;
  discount_amount: number;
  final_amount: number;
  bank?: {
    id: number;
    name: string;
    logo?: string;
  };
  offer?: {
    display: string;
  };
  message?: string;
}

export interface SavedCard {
  id: number;
  last_four: string;
  brand: string;
  nickname?: string;
  is_default: boolean;
  bank?: {
    name: string;
    logo?: string;
  };
  has_active_offer: boolean;
  potential_savings: number;
}

export interface PaymentResult {
  success: boolean;
  session_code: string;
  payment_id?: number;
  status: string;
  original_amount: number;
  discount_amount: number;
  final_amount: number;
  transaction_id?: string;
  redirect_url?: string;
  requires_redirect?: boolean;
  receipt?: {
    retailer: string;
    date: string;
    amount_paid: number;
    discount_applied: number;
  };
}

class PaymentService {
  // ==========================================
  // RETAILER APP METHODS
  // ==========================================

  async createQrSession(amount: number, description?: string): Promise<QrSession> {
    const response = await api.post('/v1/retailer/qr-sessions', {
      amount,
      description,
    });
    return response.data.data;
  }

  async getSessionStatus(sessionCode: string): Promise<QrSession> {
    const response = await api.get(`/v1/retailer/qr-sessions/${sessionCode}/status`);
    return response.data.data;
  }

  async cancelSession(sessionCode: string): Promise<void> {
    await api.post(`/v1/retailer/qr-sessions/${sessionCode}/cancel`);
  }

  async getSessionHistory(params?: {
    status?: string;
    date_from?: string;
    date_to?: string;
    per_page?: number;
  }): Promise<{ data: QrSession[]; meta: any }> {
    const response = await api.get('/v1/retailer/qr-sessions', { params });
    return response.data;
  }

  // ==========================================
  // CUSTOMER APP METHODS
  // ==========================================

  async scanQrCode(sessionCode: string): Promise<{
    session: QrSession;
    available_offers: any[];
    saved_cards: SavedCard[];
    enabled_payment_methods: Record<string, boolean>;
  }> {
    const response = await api.post(`/v1/customer/qr-sessions/${sessionCode}/scan`);
    return {
      session: response.data.data,
      available_offers: response.data.data.available_offers,
      saved_cards: response.data.data.saved_cards,
      enabled_payment_methods: response.data.data.enabled_payment_methods,
    };
  }

  async calculateDiscount(
    sessionCode: string,
    options: { card_bin?: string; tokenized_card_id?: number }
  ): Promise<DiscountResult> {
    const response = await api.post(
      `/v1/customer/qr-sessions/${sessionCode}/calculate-discount`,
      options
    );
    return response.data.data;
  }

  async payWithSavedCard(
    sessionCode: string,
    tokenizedCardId: number
  ): Promise<PaymentResult> {
    const response = await api.post(
      `/v1/customer/qr-sessions/${sessionCode}/pay-with-saved-card`,
      { tokenized_card_id: tokenizedCardId }
    );
    return response.data.data;
  }

  async payWithNewCard(
    sessionCode: string,
    options: {
      card_bin: string;
      save_card?: boolean;
      redirect_url: string;
    }
  ): Promise<PaymentResult> {
    const response = await api.post(
      `/v1/customer/qr-sessions/${sessionCode}/pay`,
      {
        gateway: 'tap',
        ...options,
      }
    );
    return response.data.data;
  }

  async payWithWallet(
    sessionCode: string,
    walletType: 'apple_pay' | 'google_pay',
    paymentToken: string
  ): Promise<PaymentResult> {
    const response = await api.post(
      `/v1/customer/qr-sessions/${sessionCode}/pay-with-wallet`,
      {
        wallet_type: walletType,
        payment_token: paymentToken,
      }
    );
    return response.data.data;
  }

  async payWithCliq(
    sessionCode: string,
    bankId: number
  ): Promise<{
    cliq_request_id: string;
    deep_link?: string;
    universal_link: string;
    final_amount: number;
  }> {
    const response = await api.post(
      `/v1/customer/qr-sessions/${sessionCode}/pay-with-cliq`,
      { bank_id: bankId }
    );
    return response.data.data;
  }

  // ==========================================
  // SAVED CARDS
  // ==========================================

  async getSavedCards(): Promise<SavedCard[]> {
    const response = await api.get('/v1/customer/cards');
    return response.data.data;
  }

  async saveCard(cardData: {
    card_number: string;
    exp_month: string;
    exp_year: string;
    cvv: string;
    cardholder_name: string;
    nickname?: string;
  }): Promise<SavedCard> {
    const response = await api.post('/v1/customer/cards', cardData);
    return response.data.data;
  }

  async deleteCard(cardId: number): Promise<void> {
    await api.delete(`/v1/customer/cards/${cardId}`);
  }

  async setDefaultCard(cardId: number): Promise<void> {
    await api.post(`/v1/customer/cards/${cardId}/set-default`);
  }

  // ==========================================
  // PAYMENT HISTORY
  // ==========================================

  async getPaymentHistory(params?: {
    status?: string;
    per_page?: number;
  }): Promise<{ data: any[]; meta: any }> {
    const response = await api.get('/v1/customer/payments', { params });
    return response.data;
  }

  async getPaymentDetails(paymentId: number): Promise<any> {
    const response = await api.get(`/v1/customer/payments/${paymentId}`);
    return response.data.data;
  }
}

export const paymentService = new PaymentService();
```

### WebSocket Service

```typescript
// src/services/pusherService.ts
import { Pusher, PusherEvent, PusherChannel } from '@pusher/pusher-websocket-react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

class PusherService {
  private pusher: Pusher | null = null;
  private channels: Map<string, PusherChannel> = new Map();

  async initialize() {
    this.pusher = Pusher.getInstance();

    await this.pusher.init({
      apiKey: 'YOUR_PUSHER_KEY',
      cluster: 'mt1',
      authEndpoint: 'https://api.trendpin.app/broadcasting/auth',
      onAuthorizer: async (channelName: string, socketId: string) => {
        const token = await AsyncStorage.getItem('auth_token');
        const response = await fetch('https://api.trendpin.app/broadcasting/auth', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channelName,
          }),
        });
        return await response.json();
      },
    });

    await this.pusher.connect();
  }

  async subscribeToRetailerChannel(
    retailerId: number,
    callbacks: {
      onScanned?: (data: any) => void;
      onProcessing?: (data: any) => void;
      onCompleted?: (data: any) => void;
      onExpired?: (data: any) => void;
      onCancelled?: (data: any) => void;
    }
  ) {
    const channelName = `private-retailer.${retailerId}.qr-sessions`;

    const channel = await this.pusher?.subscribe({
      channelName,
      onEvent: (event: PusherEvent) => {
        const data = JSON.parse(event.data);

        switch (event.eventName) {
          case 'session.scanned':
            callbacks.onScanned?.(data);
            break;
          case 'session.processing':
            callbacks.onProcessing?.(data);
            break;
          case 'session.completed':
            callbacks.onCompleted?.(data);
            break;
          case 'session.expired':
            callbacks.onExpired?.(data);
            break;
          case 'session.cancelled':
            callbacks.onCancelled?.(data);
            break;
        }
      },
    });

    if (channel) {
      this.channels.set(channelName, channel);
    }
  }

  async subscribeToCustomerChannel(
    userId: number,
    callbacks: {
      onPaymentCompleted?: (data: any) => void;
      onPaymentFailed?: (data: any) => void;
    }
  ) {
    const channelName = `private-customer.${userId}.payments`;

    const channel = await this.pusher?.subscribe({
      channelName,
      onEvent: (event: PusherEvent) => {
        const data = JSON.parse(event.data);

        switch (event.eventName) {
          case 'payment.completed':
            callbacks.onPaymentCompleted?.(data);
            break;
          case 'payment.failed':
            callbacks.onPaymentFailed?.(data);
            break;
        }
      },
    });

    if (channel) {
      this.channels.set(channelName, channel);
    }
  }

  async unsubscribe(channelName: string) {
    await this.pusher?.unsubscribe({ channelName });
    this.channels.delete(channelName);
  }

  async disconnect() {
    for (const channelName of this.channels.keys()) {
      await this.unsubscribe(channelName);
    }
    await this.pusher?.disconnect();
  }
}

export const pusherService = new PusherService();
```

### Retailer App - QR Generation Screen

```tsx
// src/screens/retailer/QrPaymentScreen.tsx
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  Image,
  StyleSheet,
  Alert,
} from 'react-native';
import { paymentService, QrSession } from '../../services/paymentService';
import { pusherService } from '../../services/pusherService';

const QrPaymentScreen: React.FC = () => {
  const [amount, setAmount] = useState('');
  const [session, setSession] = useState<QrSession | null>(null);
  const [loading, setLoading] = useState(false);
  const [expiresIn, setExpiresIn] = useState(0);

  // Subscribe to WebSocket events
  useEffect(() => {
    const retailerId = 1; // Get from auth context

    pusherService.subscribeToRetailerChannel(retailerId, {
      onScanned: (data) => {
        if (data.session_code === session?.session_code) {
          setSession((prev) => prev ? { ...prev, status: 'scanned' } : null);
          Alert.alert('QR Scanned!', `Customer ${data.customer?.name} scanned the code`);
        }
      },
      onCompleted: (data) => {
        if (data.session_code === session?.session_code) {
          setSession((prev) => prev ? { ...prev, status: 'completed', ...data } : null);
          Alert.alert(
            'Payment Received!',
            `JOD ${data.final_amount} received\nDiscount: JOD ${data.discount_amount}`
          );
        }
      },
      onExpired: (data) => {
        if (data.session_code === session?.session_code) {
          setSession(null);
          Alert.alert('Session Expired', 'Please create a new QR code');
        }
      },
    });

    return () => {
      pusherService.unsubscribe(`private-retailer.${retailerId}.qr-sessions`);
    };
  }, [session?.session_code]);

  // Countdown timer
  useEffect(() => {
    if (!session || session.status === 'completed') return;

    const interval = setInterval(() => {
      const expires = new Date(session.expires_at).getTime();
      const now = Date.now();
      const remaining = Math.max(0, Math.floor((expires - now) / 1000));
      setExpiresIn(remaining);

      if (remaining === 0) {
        setSession(null);
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [session]);

  const generateQr = async () => {
    if (!amount || parseFloat(amount) <= 0) {
      Alert.alert('Error', 'Please enter a valid amount');
      return;
    }

    setLoading(true);
    try {
      const newSession = await paymentService.createQrSession(parseFloat(amount));
      setSession(newSession);
    } catch (error: any) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to generate QR');
    } finally {
      setLoading(false);
    }
  };

  const cancelSession = async () => {
    if (!session) return;

    try {
      await paymentService.cancelSession(session.session_code);
      setSession(null);
    } catch (error) {
      Alert.alert('Error', 'Failed to cancel session');
    }
  };

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <View style={styles.container}>
      {!session ? (
        <>
          <Text style={styles.title}>Enter Amount</Text>
          <View style={styles.amountContainer}>
            <Text style={styles.currency}>JOD</Text>
            <TextInput
              style={styles.amountInput}
              value={amount}
              onChangeText={setAmount}
              keyboardType="decimal-pad"
              placeholder="0.00"
            />
          </View>
          <TouchableOpacity
            style={[styles.button, loading && styles.buttonDisabled]}
            onPress={generateQr}
            disabled={loading}
          >
            <Text style={styles.buttonText}>
              {loading ? 'Generating...' : 'Generate QR Code'}
            </Text>
          </TouchableOpacity>
        </>
      ) : (
        <>
          <View style={styles.qrContainer}>
            <Image
              source={{ uri: session.qr_code_image }}
              style={styles.qrImage}
              resizeMode="contain"
            />
          </View>

          <Text style={styles.amount}>JOD {session.amount.toFixed(2)}</Text>

          <View style={styles.statusContainer}>
            <Text style={styles.statusLabel}>Status:</Text>
            <Text style={[
              styles.status,
              session.status === 'completed' && styles.statusCompleted,
              session.status === 'scanned' && styles.statusScanned,
            ]}>
              {session.status.toUpperCase()}
            </Text>
          </View>

          {session.status !== 'completed' && (
            <Text style={styles.timer}>
              Expires in: {formatTime(expiresIn)}
            </Text>
          )}

          {session.status === 'completed' && (
            <View style={styles.completedInfo}>
              <Text style={styles.completedText}>
                Received: JOD {session.final_amount?.toFixed(2)}
              </Text>
              {session.discount_amount > 0 && (
                <Text style={styles.discountText}>
                  Discount: JOD {session.discount_amount?.toFixed(2)}
                </Text>
              )}
            </View>
          )}

          {session.status !== 'completed' && (
            <TouchableOpacity style={styles.cancelButton} onPress={cancelSession}>
              <Text style={styles.cancelButtonText}>Cancel</Text>
            </TouchableOpacity>
          )}

          {session.status === 'completed' && (
            <TouchableOpacity
              style={styles.button}
              onPress={() => {
                setSession(null);
                setAmount('');
              }}
            >
              <Text style={styles.buttonText}>New Payment</Text>
            </TouchableOpacity>
          )}
        </>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#fff',
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
  },
  amountContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 30,
  },
  currency: {
    fontSize: 28,
    fontWeight: 'bold',
    marginRight: 10,
  },
  amountInput: {
    fontSize: 48,
    fontWeight: 'bold',
    minWidth: 150,
    textAlign: 'center',
  },
  button: {
    backgroundColor: '#4CAF50',
    paddingHorizontal: 40,
    paddingVertical: 15,
    borderRadius: 10,
  },
  buttonDisabled: {
    backgroundColor: '#ccc',
  },
  buttonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  qrContainer: {
    backgroundColor: '#f5f5f5',
    padding: 20,
    borderRadius: 15,
    marginBottom: 20,
  },
  qrImage: {
    width: 250,
    height: 250,
  },
  amount: {
    fontSize: 36,
    fontWeight: 'bold',
    marginBottom: 20,
  },
  statusContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  statusLabel: {
    fontSize: 16,
    marginRight: 10,
  },
  status: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#666',
  },
  statusScanned: {
    color: '#2196F3',
  },
  statusCompleted: {
    color: '#4CAF50',
  },
  timer: {
    fontSize: 18,
    color: '#FF5722',
    marginBottom: 20,
  },
  completedInfo: {
    alignItems: 'center',
    marginBottom: 20,
  },
  completedText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#4CAF50',
  },
  discountText: {
    fontSize: 16,
    color: '#2196F3',
  },
  cancelButton: {
    backgroundColor: '#f44336',
    paddingHorizontal: 30,
    paddingVertical: 12,
    borderRadius: 10,
  },
  cancelButtonText: {
    color: '#fff',
    fontSize: 16,
  },
});

export default QrPaymentScreen;
```

### Customer App - Payment Screen

```tsx
// src/screens/customer/PaymentScreen.tsx
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  FlatList,
  Image,
  StyleSheet,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { paymentService, SavedCard, DiscountResult } from '../../services/paymentService';

interface Props {
  sessionCode: string;
  onComplete: () => void;
}

const PaymentScreen: React.FC<Props> = ({ sessionCode, onComplete }) => {
  const [loading, setLoading] = useState(true);
  const [paying, setPaying] = useState(false);
  const [session, setSession] = useState<any>(null);
  const [savedCards, setSavedCards] = useState<SavedCard[]>([]);
  const [selectedCard, setSelectedCard] = useState<SavedCard | null>(null);
  const [discount, setDiscount] = useState<DiscountResult | null>(null);

  useEffect(() => {
    loadSession();
  }, [sessionCode]);

  const loadSession = async () => {
    try {
      const { session, saved_cards } = await paymentService.scanQrCode(sessionCode);
      setSession(session);
      setSavedCards(saved_cards);

      // Auto-select default card or best offer card
      const defaultCard = saved_cards.find((c) => c.is_default);
      const bestOfferCard = saved_cards
        .filter((c) => c.has_active_offer)
        .sort((a, b) => b.potential_savings - a.potential_savings)[0];

      const cardToSelect = bestOfferCard || defaultCard || saved_cards[0];
      if (cardToSelect) {
        selectCard(cardToSelect);
      }
    } catch (error: any) {
      Alert.alert('Error', error.response?.data?.message || 'Failed to load payment');
    } finally {
      setLoading(false);
    }
  };

  const selectCard = async (card: SavedCard) => {
    setSelectedCard(card);

    try {
      const discountResult = await paymentService.calculateDiscount(sessionCode, {
        tokenized_card_id: card.id,
      });
      setDiscount(discountResult);
    } catch (error) {
      console.error('Failed to calculate discount:', error);
    }
  };

  const handlePay = async () => {
    if (!selectedCard) {
      Alert.alert('Error', 'Please select a payment method');
      return;
    }

    setPaying(true);
    try {
      const result = await paymentService.payWithSavedCard(sessionCode, selectedCard.id);

      if (result.status === 'completed') {
        Alert.alert(
          'Payment Successful!',
          `Paid JOD ${result.final_amount.toFixed(2)}\n` +
          (result.discount_amount > 0 ? `You saved JOD ${result.discount_amount.toFixed(2)}!` : ''),
          [{ text: 'OK', onPress: onComplete }]
        );
      }
    } catch (error: any) {
      Alert.alert('Payment Failed', error.response?.data?.message || 'Please try again');
    } finally {
      setPaying(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color="#4CAF50" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Retailer Info */}
      <View style={styles.retailerInfo}>
        {session?.retailer?.logo && (
          <Image source={{ uri: session.retailer.logo }} style={styles.retailerLogo} />
        )}
        <View>
          <Text style={styles.retailerName}>{session?.retailer?.name}</Text>
          <Text style={styles.branchName}>{session?.branch?.name}</Text>
        </View>
      </View>

      {/* Amount */}
      <View style={styles.amountSection}>
        {discount?.has_discount ? (
          <>
            <Text style={styles.originalAmount}>JOD {discount.original_amount.toFixed(2)}</Text>
            <Text style={styles.finalAmount}>JOD {discount.final_amount.toFixed(2)}</Text>
            <View style={styles.discountBadge}>
              <Text style={styles.discountText}>
                Save JOD {discount.discount_amount.toFixed(2)} with {discount.bank?.name}!
              </Text>
            </View>
          </>
        ) : (
          <Text style={styles.finalAmount}>JOD {session?.amount?.toFixed(2)}</Text>
        )}
      </View>

      {/* Saved Cards */}
      <Text style={styles.sectionTitle}>Select Payment Method</Text>
      <FlatList
        data={savedCards}
        keyExtractor={(item) => item.id.toString()}
        renderItem={({ item }) => (
          <TouchableOpacity
            style={[
              styles.cardItem,
              selectedCard?.id === item.id && styles.cardItemSelected,
            ]}
            onPress={() => selectCard(item)}
          >
            <View style={styles.cardInfo}>
              {item.bank?.logo && (
                <Image source={{ uri: item.bank.logo }} style={styles.bankLogo} />
              )}
              <View>
                <Text style={styles.cardNumber}>**** {item.last_four}</Text>
                <Text style={styles.cardBrand}>{item.brand.toUpperCase()}</Text>
              </View>
            </View>
            {item.has_active_offer && (
              <View style={styles.offerBadge}>
                <Text style={styles.offerText}>
                  Save JOD {item.potential_savings.toFixed(2)}
                </Text>
              </View>
            )}
          </TouchableOpacity>
        )}
        style={styles.cardList}
      />

      {/* Pay Button */}
      <TouchableOpacity
        style={[styles.payButton, paying && styles.payButtonDisabled]}
        onPress={handlePay}
        disabled={paying || !selectedCard}
      >
        {paying ? (
          <ActivityIndicator color="#fff" />
        ) : (
          <Text style={styles.payButtonText}>
            Pay JOD {(discount?.final_amount || session?.amount)?.toFixed(2)}
          </Text>
        )}
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    padding: 20,
  },
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  retailerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  retailerLogo: {
    width: 60,
    height: 60,
    borderRadius: 10,
    marginRight: 15,
  },
  retailerName: {
    fontSize: 20,
    fontWeight: 'bold',
  },
  branchName: {
    fontSize: 14,
    color: '#666',
  },
  amountSection: {
    alignItems: 'center',
    marginBottom: 30,
    paddingVertical: 20,
    backgroundColor: '#f9f9f9',
    borderRadius: 15,
  },
  originalAmount: {
    fontSize: 18,
    color: '#999',
    textDecorationLine: 'line-through',
  },
  finalAmount: {
    fontSize: 42,
    fontWeight: 'bold',
    color: '#333',
  },
  discountBadge: {
    backgroundColor: '#E8F5E9',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 20,
    marginTop: 10,
  },
  discountText: {
    color: '#4CAF50',
    fontWeight: '600',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 15,
  },
  cardList: {
    maxHeight: 250,
  },
  cardItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 15,
    backgroundColor: '#f5f5f5',
    borderRadius: 10,
    marginBottom: 10,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  cardItemSelected: {
    borderColor: '#4CAF50',
    backgroundColor: '#E8F5E9',
  },
  cardInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  bankLogo: {
    width: 40,
    height: 40,
    borderRadius: 5,
    marginRight: 12,
  },
  cardNumber: {
    fontSize: 16,
    fontWeight: '600',
  },
  cardBrand: {
    fontSize: 12,
    color: '#666',
  },
  offerBadge: {
    backgroundColor: '#4CAF50',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 15,
  },
  offerText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  payButton: {
    backgroundColor: '#4CAF50',
    paddingVertical: 18,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 20,
  },
  payButtonDisabled: {
    backgroundColor: '#ccc',
  },
  payButtonText: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
});

export default PaymentScreen;
```

---

## Testing

### Test Card Numbers (Tap Payments Sandbox)

| Card Number | Result |
|-------------|--------|
| 4111 1111 1111 1111 | Success |
| 5111 1111 1111 1118 | Success |
| 4000 0000 0000 0002 | Decline |
| 4000 0000 0000 3220 | 3DS Required |

### Postman Collection

Import the provided Postman collection for testing all endpoints:
- `postman/TrendPin_Payment_API.json`

### Running Tests

```bash
# Run all payment tests
php artisan test --filter=Payment

# Run specific test class
php artisan test Modules/Payment/Tests/Feature/QrSessionTest
```

---

## Support

For technical support or questions:
- Email: dev@trendpin.app
- Documentation: https://docs.trendpin.app/payment
