# Trendpin QR Payment System - Complete Guide

## Table of Contents
1. [Overview](#overview)
2. [How It Works](#how-it-works)
3. [Database Schema](#database-schema)
4. [API Endpoints](#api-endpoints)
5. [Testing the System](#testing-the-system)
6. [Frontend Implementation](#frontend-implementation)
7. [Security](#security)

---

## Overview

The Trendpin QR Payment System allows merchants (retailers) to generate QR codes for payments, which customers can scan and pay instantly using the Trendpin app.

### Key Features
- ✅ Merchants generate QR codes with amount and description
- ✅ QR codes are unique and time-limited (default 15 minutes)
- ✅ Customers scan QR codes to see payment details
- ✅ Instant payment processing
- ✅ Payment history for both merchants and customers
- ✅ Real-time status tracking
- ✅ Automatic expiration of old QR codes

### Use Cases
1. **In-store payments** - Customer pays at checkout
2. **Restaurant bills** - Generate QR for table bills
3. **Service payments** - Quick payments for services
4. **Invoice payments** - Generate QR for invoices

---

## How It Works

### Merchant Flow (Retailer)
```
1. Merchant enters amount (e.g., 25.50 JOD)
2. Merchant clicks "Generate QR Code"
3. QR code is generated and displayed
4. Customer scans the QR code
5. Merchant sees payment status update to "Completed"
```

### Customer Flow
```
1. Customer opens Trendpin app
2. Customer taps "Scan QR" button
3. Customer scans merchant's QR code
4. App shows: Merchant name, amount, description
5. Customer confirms payment
6. Payment is processed
7. Both merchant and customer receive confirmation
```

### QR Code Data Structure
The QR code contains JSON data:
```json
{
  "reference": "QR-ABC123XYZ",
  "merchant_id": 5,
  "merchant_name": "Café Arabica",
  "amount": 25.50,
  "currency": "JOD",
  "description": "Order #1234",
  "expires_at": "2025-11-25T20:30:00Z"
}
```

---

## Database Schema

### Table: `qr_payments`

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT | Primary key |
| merchant_id | BIGINT | Retailer who created QR (FK to users) |
| customer_id | BIGINT | Customer who paid (FK to users, nullable) |
| qr_code_reference | STRING | Unique reference (e.g., QR-ABC123) |
| amount | DECIMAL(10,2) | Payment amount |
| currency | STRING | Currency code (default: JOD) |
| description | TEXT | Payment description |
| status | ENUM | pending, completed, expired, cancelled |
| qr_code_data | TEXT | JSON payload in QR code |
| qr_code_image | STRING | Path to QR image file |
| expires_at | TIMESTAMP | When QR code expires |
| paid_at | TIMESTAMP | When payment completed |
| metadata | JSON | Additional custom data |
| created_at | TIMESTAMP | Created timestamp |
| updated_at | TIMESTAMP | Updated timestamp |

### Relationships
- `merchant_id` → `users.id` (Retailer)
- `customer_id` → `users.id` (Customer)

---

## API Endpoints

### Base URL
```
https://yourdomain.com/api
```

### Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {your_access_token}
```

---

### Merchant Endpoints

#### 1. Generate QR Code
**Endpoint:** `POST /merchant/qr-payments/generate`

**Description:** Generate a new QR code for payment

**Request Body:**
```json
{
  "amount": 25.50,
  "description": "Order #1234 - 2x Coffee, 1x Cake",
  "expiry_minutes": 15,
  "metadata": {
    "order_id": "ORD-1234",
    "table_number": "5"
  }
}
```

**Validation Rules:**
- `amount`: required, numeric, min: 0.01, max: 999999.99
- `description`: optional, string, max: 500 characters
- `expiry_minutes`: optional, integer, min: 1, max: 1440 (24 hours)
- `metadata`: optional, JSON object

**Success Response (200):**
```json
{
  "success": true,
  "message": "QR code generated successfully",
  "data": {
    "id": 123,
    "reference": "QR-ABC123XYZ",
    "amount": "25.50",
    "currency": "JOD",
    "description": "Order #1234 - 2x Coffee, 1x Cake",
    "status": "pending",
    "expires_at": "2025-11-25T20:30:00Z",
    "qr_code_image": "data:image/png;base64,iVBORw0KGgoAAAANS...",
    "qr_code_data": "{\"reference\":\"QR-ABC123XYZ\",...}"
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to generate QR code: error details"
}
```

---

#### 2. Get Merchant's QR Payments
**Endpoint:** `GET /merchant/qr-payments?status=pending&per_page=20`

**Description:** Get list of merchant's QR payments

**Query Parameters:**
- `status`: optional, filter by status (pending, completed, expired, cancelled)
- `per_page`: optional, results per page (default: 20, max: 100)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "reference": "QR-ABC123XYZ",
        "amount": "25.50",
        "status": "pending",
        "created_at": "2025-11-25T19:15:00Z",
        "expires_at": "2025-11-25T19:30:00Z",
        "customer": null
      },
      {
        "id": 122,
        "reference": "QR-XYZ789ABC",
        "amount": "15.00",
        "status": "completed",
        "created_at": "2025-11-25T18:45:00Z",
        "paid_at": "2025-11-25T18:46:30Z",
        "customer": {
          "id": 10,
          "name": "John Doe"
        }
      }
    ],
    "total": 45,
    "per_page": 20,
    "last_page": 3
  }
}
```

---

#### 3. Get QR Payment Details
**Endpoint:** `GET /merchant/qr-payments/{id}`

**Description:** Get details of a specific QR payment

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "payment": {
      "id": 123,
      "reference": "QR-ABC123XYZ",
      "amount": "25.50",
      "currency": "JOD",
      "description": "Order #1234",
      "status": "completed",
      "paid_at": "2025-11-25T19:20:15Z",
      "customer": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com"
      }
    },
    "qr_code_image": "data:image/png;base64,..." // Only if status is pending
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Payment not found"
}
```

---

#### 4. Cancel QR Payment
**Endpoint:** `POST /merchant/qr-payments/{id}/cancel`

**Description:** Cancel a pending QR payment

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment cancelled successfully"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Payment cannot be cancelled"
}
```

---

#### 5. Check Payment Status
**Endpoint:** `GET /merchant/qr-payments/status/{reference}`

**Description:** Check real-time status of a QR payment

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "reference": "QR-ABC123XYZ",
    "status": "completed",
    "amount": "25.50",
    "paid_at": "2025-11-25T19:20:15Z",
    "customer": {
      "id": 10,
      "name": "John Doe"
    }
  }
}
```

---

### Customer Endpoints

#### 1. Verify QR Code
**Endpoint:** `POST /customer/qr-payments/verify`

**Description:** Verify QR code and get payment details before payment

**Request Body:**
```json
{
  "qr_data": "{\"reference\":\"QR-ABC123XYZ\",\"merchant_id\":5,...}"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "reference": "QR-ABC123XYZ",
    "merchant": {
      "id": 5,
      "name": "Café Arabica"
    },
    "amount": "25.50",
    "currency": "JOD",
    "description": "Order #1234",
    "expires_at": "2025-11-25T19:30:00Z"
  }
}
```

**Error Responses:**
```json
// Invalid QR code
{
  "success": false,
  "message": "Invalid QR code"
}

// Expired
{
  "success": false,
  "message": "This QR code has expired"
}

// Already paid
{
  "success": false,
  "message": "This payment has already been completed"
}
```

---

#### 2. Process Payment
**Endpoint:** `POST /customer/qr-payments/pay`

**Description:** Complete the payment

**Request Body:**
```json
{
  "reference": "QR-ABC123XYZ",
  "payment_method": "wallet"  // Optional: wallet, card, bank
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Payment completed successfully",
  "data": {
    "payment": {
      "id": 123,
      "reference": "QR-ABC123XYZ",
      "amount": "25.50",
      "status": "completed",
      "paid_at": "2025-11-25T19:20:15Z",
      "merchant": {
        "id": 5,
        "name": "Café Arabica"
      }
    }
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Payment failed: Payment not found"
}
```

---

#### 3. Get Payment History
**Endpoint:** `GET /customer/qr-payments/history?per_page=20`

**Description:** Get customer's payment history

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "reference": "QR-ABC123XYZ",
        "amount": "25.50",
        "merchant": {
          "id": 5,
          "name": "Café Arabica"
        },
        "description": "Order #1234",
        "paid_at": "2025-11-25T19:20:15Z"
      }
    ],
    "total": 15,
    "per_page": 20
  }
}
```

---

#### 4. Get Payment Details
**Endpoint:** `GET /customer/qr-payments/{reference}`

**Description:** Get details of a specific payment

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "reference": "QR-ABC123XYZ",
    "merchant": {
      "name": "Café Arabica"
    },
    "amount": "25.50",
    "currency": "JOD",
    "description": "Order #1234",
    "status": "completed",
    "paid_at": "2025-11-25T19:20:15Z"
  }
}
```

---

## Testing the System

### Using Postman/curl

#### Step 1: Get Authentication Token
First, login to get a Bearer token:
```bash
curl -X POST http://localhost/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "retailer@example.com",
    "password": "password123"
  }'
```

Save the `access_token` from the response.

#### Step 2: Generate QR Code (As Merchant)
```bash
curl -X POST http://localhost/api/merchant/qr-payments/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 25.50,
    "description": "Test Order #1",
    "expiry_minutes": 15
  }'
```

#### Step 3: Copy QR Data
From the response, copy the `qr_code_data` field.

#### Step 4: Verify QR Code (As Customer)
Login as a customer and get their token, then:
```bash
curl -X POST http://localhost/api/customer/qr-payments/verify \
  -H "Authorization: Bearer CUSTOMER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "PASTE_QR_DATA_HERE"
  }'
```

#### Step 5: Complete Payment (As Customer)
```bash
curl -X POST http://localhost/api/customer/qr-payments/pay \
  -H "Authorization: Bearer CUSTOMER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reference": "QR-ABC123XYZ"
  }'
```

#### Step 6: Check Status (As Merchant)
```bash
curl -X GET "http://localhost/api/merchant/qr-payments/status/QR-ABC123XYZ" \
  -H "Authorization: Bearer MERCHANT_TOKEN"
```

### Testing with Mobile App

#### Merchant App Flow
1. Login as retailer
2. Navigate to "Receive Payment" screen
3. Enter amount: 25.50 JOD
4. Enter description: "Order #1234"
5. Click "Generate QR Code"
6. Show QR code to customer
7. Wait for payment confirmation

#### Customer App Flow
1. Login as customer
2. Tap "Scan QR" button
3. Point camera at merchant's QR code
4. Review payment details
5. Tap "Pay Now"
6. See success confirmation

---

## Frontend Implementation

### Example: Merchant QR Generator (React)

```jsx
import React, { useState } from 'react';
import { router } from '@inertiajs/react';

function QRCodeGenerator() {
    const [amount, setAmount] = useState('');
    const [description, setDescription] = useState('');
    const [qrCode, setQrCode] = useState(null);
    const [loading, setLoading] = useState(false);

    const generateQR = async () => {
        setLoading(true);

        try {
            const response = await fetch('/api/merchant/qr-payments/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
                body: JSON.stringify({
                    amount: parseFloat(amount),
                    description: description,
                    expiry_minutes: 15,
                }),
            });

            const data = await response.json();

            if (data.success) {
                setQrCode(data.data);
                // Start polling for payment status
                pollPaymentStatus(data.data.reference);
            }
        } catch (error) {
            console.error('Error generating QR:', error);
        } finally {
            setLoading(false);
        }
    };

    const pollPaymentStatus = (reference) => {
        const interval = setInterval(async () => {
            const response = await fetch(`/api/merchant/qr-payments/status/${reference}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
            });

            const data = await response.json();

            if (data.success && data.data.status === 'completed') {
                clearInterval(interval);
                alert('Payment received!');
                // Refresh or redirect
            }
        }, 3000); // Check every 3 seconds

        // Stop polling after 20 minutes
        setTimeout(() => clearInterval(interval), 20 * 60 * 1000);
    };

    return (
        <div className="max-w-md mx-auto p-6">
            <h2 className="text-2xl font-bold mb-4">Generate Payment QR Code</h2>

            {!qrCode ? (
                <div>
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-2">Amount (JOD)</label>
                        <input
                            type="number"
                            value={amount}
                            onChange={(e) => setAmount(e.target.value)}
                            className="w-full px-4 py-2 border rounded-lg"
                            placeholder="25.50"
                            step="0.01"
                        />
                    </div>

                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-2">Description</label>
                        <input
                            type="text"
                            value={description}
                            onChange={(e) => setDescription(e.target.value)}
                            className="w-full px-4 py-2 border rounded-lg"
                            placeholder="Order #1234"
                        />
                    </div>

                    <button
                        onClick={generateQR}
                        disabled={loading || !amount}
                        className="w-full py-3 bg-pink-600 text-white rounded-lg font-medium hover:bg-pink-700 disabled:opacity-50"
                    >
                        {loading ? 'Generating...' : 'Generate QR Code'}
                    </button>
                </div>
            ) : (
                <div className="text-center">
                    <p className="text-lg font-semibold mb-4">
                        {qrCode.amount} {qrCode.currency}
                    </p>
                    <p className="text-gray-600 mb-4">{qrCode.description}</p>

                    <div className="bg-white p-4 rounded-lg shadow-lg mb-4">
                        <img
                            src={qrCode.qr_code_image}
                            alt="QR Code"
                            className="mx-auto"
                            style={{ width: '300px', height: '300px' }}
                        />
                    </div>

                    <p className="text-sm text-gray-500 mb-4">
                        Expires at: {new Date(qrCode.expires_at).toLocaleString()}
                    </p>

                    <div className="flex gap-2">
                        <button
                            onClick={() => setQrCode(null)}
                            className="flex-1 py-2 border border-gray-300 rounded-lg"
                        >
                            New QR Code
                        </button>
                        <button
                            onClick={() => window.print()}
                            className="flex-1 py-2 bg-gray-600 text-white rounded-lg"
                        >
                            Print
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

export default QRCodeGenerator;
```

### Example: Customer QR Scanner (React Native)

```jsx
import React, { useState } from 'react';
import { View, Text, Button, Alert } from 'react-native';
import { BarCodeScanner } from 'expo-barcode-scanner';

function QRScanner() {
    const [hasPermission, setHasPermission] = useState(null);
    const [scanned, setScanned] = useState(false);
    const [paymentDetails, setPaymentDetails] = useState(null);

    React.useEffect(() => {
        (async () => {
            const { status } = await BarCodeScanner.requestPermissionsAsync();
            setHasPermission(status === 'granted');
        })();
    }, []);

    const handleBarCodeScanned = async ({ data }) => {
        setScanned(true);

        try {
            // Verify QR code
            const response = await fetch('/api/customer/qr-payments/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({ qr_data: data }),
            });

            const result = await response.json();

            if (result.success) {
                setPaymentDetails(result.data);
            } else {
                Alert.alert('Error', result.message);
                setScanned(false);
            }
        } catch (error) {
            Alert.alert('Error', 'Failed to verify QR code');
            setScanned(false);
        }
    };

    const processPayment = async () => {
        try {
            const response = await fetch('/api/customer/qr-payments/pay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
                body: JSON.stringify({
                    reference: paymentDetails.reference,
                }),
            });

            const result = await response.json();

            if (result.success) {
                Alert.alert('Success', 'Payment completed!');
                // Navigate back or to receipt screen
            } else {
                Alert.alert('Error', result.message);
            }
        } catch (error) {
            Alert.alert('Error', 'Payment failed');
        }
    };

    if (hasPermission === null) {
        return <Text>Requesting camera permission...</Text>;
    }

    if (hasPermission === false) {
        return <Text>No access to camera</Text>;
    }

    if (!scanned) {
        return (
            <View style={{ flex: 1 }}>
                <BarCodeScanner
                    onBarCodeScanned={handleBarCodeScanned}
                    style={{ flex: 1 }}
                />
                <View style={{ padding: 20, backgroundColor: 'rgba(0,0,0,0.7)' }}>
                    <Text style={{ color: 'white', textAlign: 'center' }}>
                        Scan merchant's QR code to pay
                    </Text>
                </View>
            </View>
        );
    }

    if (paymentDetails) {
        return (
            <View style={{ flex: 1, padding: 20, justifyContent: 'center' }}>
                <Text style={{ fontSize: 24, fontWeight: 'bold', marginBottom: 20 }}>
                    Confirm Payment
                </Text>

                <Text style={{ fontSize: 18, marginBottom: 10 }}>
                    Merchant: {paymentDetails.merchant.name}
                </Text>

                <Text style={{ fontSize: 32, fontWeight: 'bold', color: '#E91E8C', marginBottom: 10 }}>
                    {paymentDetails.amount} {paymentDetails.currency}
                </Text>

                {paymentDetails.description && (
                    <Text style={{ fontSize: 14, color: '#666', marginBottom: 20 }}>
                        {paymentDetails.description}
                    </Text>
                )}

                <Button title="Pay Now" onPress={processPayment} color="#E91E8C" />

                <Button
                    title="Cancel"
                    onPress={() => {
                        setScanned(false);
                        setPaymentDetails(null);
                    }}
                    color="#666"
                />
            </View>
        );
    }

    return null;
}

export default QRScanner;
```

---

## Security

### Best Practices

1. **Authentication**: Always require Bearer token authentication
2. **Authorization**: Verify merchant owns QR payment before showing/cancelling
3. **Expiration**: QR codes automatically expire (default 15 minutes)
4. **HTTPS**: Always use HTTPS in production
5. **Rate Limiting**: Implement rate limiting on API endpoints
6. **Input Validation**: Validate all inputs on backend
7. **CORS**: Configure CORS properly for your frontend domain

### Rate Limiting Example

Add to `routes/api.php`:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes here
});
```

### Scheduled Task (Expire Old QR Codes)

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(\App\Services\QrPaymentService::class)->expireOldQrCodes();
    })->everyFiveMinutes();
}
```

---

## Troubleshooting

### QR Code Image Not Generating
**Problem**: QR code image is null
**Solution**: Ensure storage is writable:
```bash
docker exec trendpin_api chmod -R 775 /var/www/api/storage
docker exec trendpin_api chown -R www-data:www-data /var/www/api/storage
```

### Payment Status Not Updating
**Problem**: Frontend doesn't show completed status
**Solution**: Implement polling or websockets for real-time updates

### QR Code Expired Too Quickly
**Problem**: QR expires before customer can pay
**Solution**: Increase `expiry_minutes` when generating:
```json
{
  "amount": 25.50,
  "expiry_minutes": 30
}
```

---

## Next Steps

1. **Implement Real Payment Gateway** - Currently marks as paid directly, integrate with actual gateway
2. **Add Websockets** - For real-time payment notifications
3. **Mobile Apps** - Build React Native apps for merchant and customer
4. **Print QR Codes** - Add functionality to print QR codes for physical display
5. **Analytics** - Track payment statistics and trends
6. **Refunds** - Add refund functionality
7. **Split Payments** - Allow splitting payment among multiple customers

---

**Version**: 1.0
**Last Updated**: November 25, 2025
**Status**: Ready for Testing
