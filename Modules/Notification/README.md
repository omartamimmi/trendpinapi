# Notification Module

A comprehensive multi-channel notification system for TrendPin that supports push notifications, SMS, email, and in-app messages with location-based targeting, user preferences, and delivery tracking.

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Admin Dashboard](#admin-dashboard)
  - [API Endpoints](#api-endpoints)
  - [Provider Setup](#provider-setup)
- [Database Schema](#database-schema)
- [Service Classes](#service-classes)
- [Notification Tags](#notification-tags)
- [Testing](#testing)
- [Examples](#examples)

## Features

### Core Features
- ✅ **Multi-Channel Support**: Push, SMS, Email, In-App notifications
- ✅ **Provider Management**: Support for multiple providers (FCM, Twilio, SendGrid)
- ✅ **Location-Based Targeting**: Send notifications to users within a specific radius
- ✅ **User Preferences**: Per-channel and per-tag opt-in/opt-out
- ✅ **Delivery Tracking**: Track sent, delivered, read, and clicked status
- ✅ **Template System**: Reusable templates with placeholder support
- ✅ **Nearby Notifications**: Automatically notify users about nearby brands/offers
- ✅ **Batch Sending**: Efficient batch processing for large audiences
- ✅ **Provider Failover**: Automatic fallback to backup providers
- ✅ **Analytics**: Detailed delivery statistics and campaign performance

### Advanced Features
- 📊 Real-time delivery statistics
- 🎯 Multiple targeting options (all, location, individual, segment)
- 🔄 Provider priority and fallback system
- 🔐 Encrypted credential storage
- 📝 Template placeholder replacement
- 🌍 Haversine distance calculation for location targeting
- 📱 FCM token management with user location

## Architecture

### Module Structure
```
Modules/Notification/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminNotificationController.php    # Admin API endpoints
│   │   └── CustomerNotificationController.php # Customer API endpoints
│   ├── Models/
│   │   ├── NotificationProvider.php           # Provider configuration
│   │   ├── NotificationMessage.php            # Notification campaigns
│   │   ├── NotificationDelivery.php           # Individual deliveries
│   │   ├── NotificationTemplate.php           # Reusable templates
│   │   └── UserNotificationPreference.php     # User preferences
│   └── Services/
│       ├── NotificationProviderInterface.php  # Provider interface
│       ├── FCMProvider.php                    # Firebase implementation
│       ├── NotificationService.php            # Core service
│       └── NearbyNotificationService.php      # Location-based logic
├── database/migrations/
│   ├── 2025_12_10_000001_create_notification_providers_table.php
│   ├── 2025_12_10_000002_create_notification_messages_table.php
│   ├── 2025_12_10_000003_create_notification_deliveries_table.php
│   ├── 2025_12_10_000004_create_notification_templates_table.php
│   └── 2025_12_10_000005_create_user_notification_preferences_table.php
└── routes/
    └── api.php                                # API routes
```

### Component Diagram
```
┌─────────────────────────────────────────────────────────────┐
│                     Admin Dashboard                          │
│  - Manage Providers  - Send Notifications  - Templates      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  NotificationService                         │
│  - Target Users  - Check Preferences  - Send to Channels    │
└────────────────────────┬────────────────────────────────────┘
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
    ┌──────────┐  ┌──────────┐  ┌──────────┐
    │   FCM    │  │  Twilio  │  │ SendGrid │
    │ Provider │  │ Provider │  │ Provider │
    └──────────┘  └──────────┘  └──────────┘
```

## Installation

### 1. Run Migrations
```bash
php artisan migrate
```

This creates 5 database tables:
- `notification_providers`
- `notification_messages`
- `notification_deliveries`
- `notification_templates`
- `user_notification_preferences`

### 2. Register Routes
Routes are automatically loaded from `routes/api.php`. Ensure your main `routes/api.php` includes:
```php
require __DIR__.'/../Modules/Notification/routes/api.php';
```

### 3. Set Permissions
Admin routes require the `admin` role. Ensure your user has the admin role:
```php
$user->assignRole('admin');
```

## Configuration

### Environment Variables (Optional)
Add default FCM credentials to `.env`:
```env
FCM_SERVER_KEY=your_firebase_server_key
FCM_SENDER_ID=your_firebase_sender_id
```

## Usage

### Admin Dashboard

Access the admin notification management at:
- **Notifications List**: `/admin/notifications`
- **Send Notification**: `/admin/notifications/send`
- **Manage Providers**: `/admin/notification-providers`
- **Manage Templates**: `/admin/notification-templates`

### API Endpoints

#### Admin Endpoints (require `auth:sanctum` + `role:admin`)

**Notification Providers**
```http
GET    /api/v1/admin/notification-providers          # List all providers
POST   /api/v1/admin/notification-providers          # Create provider
PUT    /api/v1/admin/notification-providers/{id}     # Update provider
DELETE /api/v1/admin/notification-providers/{id}     # Delete provider
POST   /api/v1/admin/notification-providers/{id}/test # Test credentials
```

**Notifications**
```http
POST   /api/v1/admin/notifications/send              # Send notification
GET    /api/v1/admin/notifications                   # List notifications
GET    /api/v1/admin/notifications/{id}              # Get notification details
GET    /api/v1/admin/notifications/{id}/stats        # Get delivery stats
```

**Templates**
```http
GET    /api/v1/admin/notification-templates          # List templates
POST   /api/v1/admin/notification-templates          # Create template
PUT    /api/v1/admin/notification-templates/{id}     # Update template
DELETE /api/v1/admin/notification-templates/{id}     # Delete template
```

#### Customer Endpoints (require `auth:sanctum`)

**User Notifications**
```http
GET    /api/v1/notifications                         # List user notifications
GET    /api/v1/notifications/unread-count            # Get unread count
GET    /api/v1/notifications/{id}                    # View notification (auto-marks read)
POST   /api/v1/notifications/{id}/mark-read          # Mark as read
POST   /api/v1/notifications/mark-all-read           # Mark all as read
DELETE /api/v1/notifications/{id}                    # Delete notification
```

**User Preferences**
```http
GET    /api/v1/user/notification-preferences         # Get preferences matrix
PUT    /api/v1/user/notification-preferences         # Update preferences
POST   /api/v1/user/fcm-token                        # Update FCM token & location
```

### Provider Setup

#### Firebase Cloud Messaging (FCM)

1. **Get FCM Server Key**
   - Go to [Firebase Console](https://console.firebase.google.com)
   - Select your project
   - Project Settings → Cloud Messaging
   - Copy the Server Key

2. **Add Provider via API**
```bash
curl -X POST http://localhost/api/v1/admin/notification-providers \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "push",
    "provider": "fcm",
    "name": "Firebase Cloud Messaging",
    "credentials": {
      "server_key": "YOUR_FCM_SERVER_KEY"
    },
    "is_active": true,
    "priority": 1
  }'
```

3. **Test Provider**
```bash
curl -X POST http://localhost/api/v1/admin/notification-providers/1/test \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

#### Other Providers

The system is designed to support multiple providers. To add a new provider:

1. Create a provider class implementing `NotificationProviderInterface`
2. Place it in `Modules/Notification/app/Services/`
3. Name it `{ProviderName}Provider.php` (e.g., `TwilioProvider.php`)

Example for Twilio:
```php
<?php
namespace Modules\Notification\app\Services;

class TwilioProvider implements NotificationProviderInterface
{
    protected $accountSid;
    protected $authToken;

    public function __construct(array $credentials)
    {
        $this->accountSid = $credentials['account_sid'];
        $this->authToken = $credentials['auth_token'];
    }

    public function send(string $recipient, array $message): array
    {
        // Implement Twilio SMS sending
    }

    // ... implement other interface methods
}
```

## Database Schema

### notification_providers
Stores provider configurations (FCM, Twilio, etc.)
```sql
- id: bigint (PK)
- type: enum('push', 'sms', 'email', 'whatsapp')
- provider: string (fcm, twilio, sendgrid)
- name: string
- credentials: text (encrypted JSON)
- is_active: boolean
- priority: integer (1 = primary, 2+ = fallback)
- settings: json
- last_tested_at: timestamp
- last_test_result: text
```

### notification_messages
Notification campaigns/broadcasts
```sql
- id: bigint (PK)
- template_id: bigint (FK, nullable)
- tag: string (promotional, nearby, etc.)
- title: string
- body: text
- channels: json array ['push', 'sms']
- target_type: enum('all', 'location', 'individual', 'segment')
- target_criteria: json
- status: enum('draft', 'scheduled', 'sending', 'sent', 'failed')
- total_recipients: integer
- scheduled_at: timestamp
- sent_at: timestamp
- delivery_stats: json
- image_url: string
- deep_link: string
- action_data: json
- created_by: bigint (FK to users)
```

### notification_deliveries
Individual delivery tracking per user
```sql
- id: bigint (PK)
- notification_message_id: bigint (FK)
- user_id: bigint (FK)
- channel: enum('push', 'sms', 'email')
- provider_id: bigint (FK)
- status: enum('pending', 'sent', 'delivered', 'read', 'clicked', 'failed')
- provider_response: text
- provider_message_id: string
- failed_reason: text
- sent_at: timestamp
- delivered_at: timestamp
- read_at: timestamp
- clicked_at: timestamp
```

### notification_templates
Reusable notification templates
```sql
- id: bigint (PK)
- name: string
- tag: string
- title_template: string (supports {{placeholders}})
- body_template: text (supports {{placeholders}})
- action_type: string
- action_data: json
- image_url: string
- deep_link_template: string
- is_active: boolean
- created_by: bigint (FK)
```

### user_notification_preferences
User opt-in/opt-out preferences
```sql
- id: bigint (PK)
- user_id: bigint (FK)
- channel: enum('push', 'sms', 'email')
- tag: string
- is_enabled: boolean
- UNIQUE(user_id, channel, tag)
```

## Service Classes

### NotificationService
Main orchestration service that handles:
- User targeting based on criteria
- Preference checking
- Channel-specific sending
- Provider selection and failover
- Delivery tracking

Key Methods:
```php
sendNotification(NotificationMessage $notification): void
getTargetUsers(NotificationMessage $notification): Collection
sendToChannel(NotificationMessage $notification, $users, $channel): array
```

### FCMProvider
Firebase Cloud Messaging implementation:
- Sends push notifications via FCM API
- Supports batch sending (up to 1000 tokens per request)
- Validates credentials using dry run
- Handles FCM-specific response format

### NearbyNotificationService
Location-based notification service:
- Finds branches/brands within radius using Haversine formula
- Creates targeted notifications for nearby users
- Supports nearby brands and nearby offers

Key Methods:
```php
sendNearbyBrandsNotification($lat, $lng, $radiusKm): array
sendNearbyOffersNotification($lat, $lng, $radiusKm): array
getBranchesNearby($lat, $lng, $radiusKm): Collection
```

## Notification Tags

Tags categorize notification purpose and allow users to opt-in/opt-out:

| Tag | Description | Use Case |
|-----|-------------|----------|
| `nearby` | Location-based notifications | Brands/offers near user |
| `new_offer` | New offer announcements | Fresh deals available |
| `offer_expiring` | Expiring offer reminders | Limited time alerts |
| `brand_update` | Brand news/updates | Store hours, new products |
| `promotional` | Marketing campaigns | General promotions |
| `personalized` | Personalized recommendations | AI-based suggestions |
| `system` | System notifications | Account updates, security |

## Testing

### Test Provider Credentials
```bash
# Via API
curl -X POST http://localhost/api/v1/admin/notification-providers/1/test \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected Response
{
  "valid": true,
  "message": "Credentials are valid"
}
```

### Send Test Notification
```bash
curl -X POST http://localhost/api/v1/admin/notifications/send \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tag": "system",
    "title": "Test Notification",
    "body": "This is a test message",
    "channels": ["push"],
    "target_type": "all"
  }'
```

### Check Delivery Stats
```bash
curl http://localhost/api/v1/admin/notifications/1/stats \
  -H "Authorization: Bearer YOUR_TOKEN"

# Response
{
  "total": 100,
  "sent": 95,
  "delivered": 90,
  "read": 45,
  "clicked": 12,
  "failed": 5,
  "by_channel": {
    "push": {
      "total": 100,
      "sent": 95,
      "failed": 5
    }
  }
}
```

## Examples

### Example 1: Send Location-Based Notification

```php
use Modules\Notification\app\Models\NotificationMessage;

$notification = NotificationMessage::create([
    'tag' => 'nearby',
    'title' => 'Discover brands near you!',
    'body' => 'Check out 5 brands within 3km of your location',
    'channels' => ['push'],
    'target_type' => 'location',
    'target_criteria' => [
        'lat' => 31.9539,
        'lng' => 35.9106,
        'radius' => 3, // km
    ],
    'deep_link' => 'app://brands/nearby',
    'created_by' => auth()->id(),
]);

$notificationService->sendNotification($notification);
```

### Example 2: Send to Specific Users

```php
$notification = NotificationMessage::create([
    'tag' => 'promotional',
    'title' => 'Special offer just for you!',
    'body' => 'Get 20% off your next purchase',
    'channels' => ['push', 'email'],
    'target_type' => 'individual',
    'target_criteria' => [
        'user_ids' => [1, 2, 3, 4, 5]
    ],
    'created_by' => auth()->id(),
]);

$notificationService->sendNotification($notification);
```

### Example 3: Use Template with Placeholders

```php
// Create template
$template = NotificationTemplate::create([
    'name' => 'New Offer Template',
    'tag' => 'new_offer',
    'title_template' => 'New offer from {{brand_name}}!',
    'body_template' => 'Get {{discount}}% off at {{brand_name}}. Valid until {{expiry_date}}.',
    'deep_link_template' => 'app://offers/{{offer_id}}',
    'is_active' => true,
]);

// Render with data
$rendered = $template->render([
    'brand_name' => 'Coffee House',
    'discount' => '25',
    'expiry_date' => 'Dec 31',
    'offer_id' => '123',
]);

// Result:
// title: "New offer from Coffee House!"
// body: "Get 25% off at Coffee House. Valid until Dec 31."
// deep_link: "app://offers/123"
```

### Example 4: Update User FCM Token (Mobile App)

```javascript
// React Native / Mobile App
const updateFCMToken = async (token, latitude, longitude) => {
  await fetch('http://localhost/api/v1/user/fcm-token', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${userToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      fcm_token: token,
      lat: latitude,
      lng: longitude,
    }),
  });
};
```

### Example 5: Manage User Preferences

```javascript
// Update notification preferences
const updatePreferences = async () => {
  await fetch('http://localhost/api/v1/user/notification-preferences', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${userToken}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      preferences: [
        { channel: 'push', tag: 'nearby', enabled: true },
        { channel: 'push', tag: 'promotional', enabled: false },
        { channel: 'email', tag: 'new_offer', enabled: true },
      ],
    }),
  });
};
```

### Example 6: Send Nearby Brands Notification (Service)

```php
use Modules\Notification\app\Services\NearbyNotificationService;

$nearbyService = new NearbyNotificationService($notificationService);

// Send notification to users within 5km
$result = $nearbyService->sendNearbyBrandsNotification(
    31.9539,  // latitude
    35.9106,  // longitude
    5,        // radius in km
    ['push']  // channels
);

// Result:
// [
//   'success' => true,
//   'notification' => NotificationMessage,
//   'branches_count' => 12,
//   'brands_count' => 8
// ]
```

## Troubleshooting

### Notifications not sending

1. **Check provider is active**
```sql
SELECT * FROM notification_providers WHERE is_active = 1;
```

2. **Test provider credentials**
```bash
curl -X POST http://localhost/api/v1/admin/notification-providers/1/test
```

3. **Check delivery table for errors**
```sql
SELECT * FROM notification_deliveries WHERE status = 'failed';
```

### Users not receiving notifications

1. **Check user preferences**
```sql
SELECT * FROM user_notification_preferences WHERE user_id = 1;
```
Default is enabled (opt-out model), so only disabled preferences are stored.

2. **Verify FCM token exists**
```sql
SELECT * FROM notification_based_location WHERE user_id = 1;
```

3. **Check notification status**
```sql
SELECT status, delivery_stats FROM notification_messages WHERE id = 1;
```

### Location-based targeting not working

1. **Ensure user has location data**
```sql
SELECT * FROM notification_based_location WHERE lat IS NOT NULL;
```

2. **Verify brands have coordinates**
```sql
SELECT * FROM brands WHERE lat IS NOT NULL AND lng IS NOT NULL;
```

3. **Test Haversine calculation**
```sql
SELECT
  ( 6371 * acos( cos( radians(31.9539) ) * cos( radians( CAST(lat AS DECIMAL(10,8)) ) )
  * cos( radians( CAST(lng AS DECIMAL(11,8)) ) - radians(35.9106) )
  + sin( radians(31.9539) ) * sin( radians( CAST(lat AS DECIMAL(10,8)) ) ) ) ) AS distance
FROM notification_based_location
HAVING distance < 5;
```

## License

This module is part of the TrendPin project.

## Support

For issues or questions:
- Check the [API documentation](#api-endpoints)
- Review [examples](#examples)
- Contact the development team
