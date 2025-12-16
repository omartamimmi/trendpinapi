# Geofence Mobile Integration Guide

This guide explains how to integrate the Trendpin Geofence module with your mobile application using Radar.io.

## Overview

The geofence system works as follows:
1. Mobile app tracks user location using Radar.io SDK
2. When user enters a geofence, Radar.io sends a webhook to the backend
3. Backend checks user interests, throttling rules, and sends push notification if appropriate
4. User receives notification about nearby offers

## Prerequisites

- Radar.io account with API keys
- Firebase Cloud Messaging (FCM) for push notifications
- Mobile app with location permissions

---

## 1. Radar.io Setup

### Get API Keys

1. Go to [Radar.io Dashboard](https://radar.io/dashboard)
2. Create a new project or use existing one
3. Copy these keys:
   - **Publishable Key** (for mobile SDK): `prj_live_pk_...`
   - **Secret Key** (for backend): `prj_live_sk_...`

### Configure Webhook

1. In Radar Dashboard, go to **Settings > Webhooks**
2. Add a new webhook with URL:
   ```
   https://your-api-domain.com/webhooks/radar
   ```
3. Select events to receive:
   - `user.entered_geofence`
   - `user.exited_geofence`
   - `user.dwelled_in_geofence`
4. Copy the **Webhook Secret** for signature verification

### Add Keys to Backend

In the admin dashboard (`/admin/geofence/settings`), enter:
- Radar Secret Key
- Radar Publishable Key
- Radar Webhook Secret

---

## 2. Mobile SDK Installation

### iOS (Swift)

```bash
# CocoaPods
pod 'RadarSDK', '~> 3.0'

# Swift Package Manager
https://github.com/radarlabs/radar-sdk-ios.git
```

### Android (Kotlin/Java)

```gradle
// build.gradle (app)
dependencies {
    implementation 'io.radar:sdk:3.+'
}
```

### React Native

```bash
npm install react-native-radar
# or
yarn add react-native-radar
```

### Flutter

```yaml
# pubspec.yaml
dependencies:
  flutter_radar: ^3.0.0
```

---

## 3. Initialize Radar SDK

### iOS (Swift)

```swift
import RadarSDK

@main
class AppDelegate: UIResponder, UIApplicationDelegate {
    func application(_ application: UIApplication,
                     didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {

        // Initialize Radar with publishable key
        Radar.initialize(publishableKey: "prj_live_pk_xxxxxxxxxx")

        // Set log level for debugging
        Radar.setLogLevel(.debug)

        return true
    }
}
```

### Android (Kotlin)

```kotlin
import io.radar.sdk.Radar

class MyApplication : Application() {
    override fun onCreate() {
        super.onCreate()

        // Initialize Radar with publishable key
        Radar.initialize(this, "prj_live_pk_xxxxxxxxxx")

        // Set log level for debugging
        Radar.setLogLevel(Radar.RadarLogLevel.DEBUG)
    }
}
```

### React Native

```javascript
import Radar from 'react-native-radar';

// Initialize in App.js or index.js
Radar.initialize('prj_live_pk_xxxxxxxxxx');
```

### Flutter

```dart
import 'package:flutter_radar/flutter_radar.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await Radar.initialize('prj_live_pk_xxxxxxxxxx');

  runApp(MyApp());
}
```

---

## 4. Link User to Radar (CRITICAL)

**This is the most important step.** You must link the authenticated Trendpin user to Radar so the backend can identify who triggered the geofence event.

### After User Login

```javascript
// React Native Example
import Radar from 'react-native-radar';

const onUserLogin = async (user) => {
  // Set Radar user ID to match your backend user ID
  await Radar.setUserId(user.id.toString());

  // Set metadata for additional context
  await Radar.setMetadata({
    user_id: user.id,
    email: user.email,
    name: user.name,
  });

  // Optionally set description
  await Radar.setDescription(user.name);
};
```

### iOS (Swift)

```swift
func onUserLogin(user: User) {
    // Set user ID to match backend
    Radar.setUserId(String(user.id))

    // Set metadata
    Radar.setMetadata([
        "user_id": user.id,
        "email": user.email,
        "name": user.name
    ])

    Radar.setDescription(user.name)
}
```

### Android (Kotlin)

```kotlin
fun onUserLogin(user: User) {
    // Set user ID to match backend
    Radar.setUserId(user.id.toString())

    // Set metadata
    Radar.setMetadata(JSONObject().apply {
        put("user_id", user.id)
        put("email", user.email)
        put("name", user.name)
    })

    Radar.setDescription(user.name)
}
```

### On User Logout

```javascript
// Clear Radar user data on logout
await Radar.setUserId(null);
await Radar.setMetadata(null);
await Radar.stopTracking();
```

---

## 5. Request Location Permissions

### iOS

Add to `Info.plist`:

```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>We need your location to show you nearby offers and deals.</string>

<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>
<string>We need your location to notify you about nearby offers even when the app is closed.</string>

<key>UIBackgroundModes</key>
<array>
    <string>location</string>
    <string>fetch</string>
    <string>remote-notification</string>
</array>
```

### Android

Add to `AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
<uses-permission android:name="android.permission.RECEIVE_BOOT_COMPLETED" />
```

### Request Permission in App

```javascript
// React Native
import { PermissionsAndroid, Platform } from 'react-native';
import Radar from 'react-native-radar';

const requestLocationPermission = async () => {
  if (Platform.OS === 'ios') {
    // iOS handles permission request automatically
    const status = await Radar.requestPermissions(true); // true = background
    return status === 'GRANTED_BACKGROUND';
  }

  if (Platform.OS === 'android') {
    // Request foreground permission first
    const fineLocation = await PermissionsAndroid.request(
      PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
      {
        title: 'Location Permission',
        message: 'Trendpin needs access to your location to show nearby offers.',
        buttonPositive: 'Allow',
      }
    );

    // Then request background permission (Android 10+)
    if (fineLocation === PermissionsAndroid.RESULTS.GRANTED) {
      if (Platform.Version >= 29) {
        const backgroundLocation = await PermissionsAndroid.request(
          PermissionsAndroid.PERMISSIONS.ACCESS_BACKGROUND_LOCATION,
          {
            title: 'Background Location',
            message: 'Allow Trendpin to access location in background for nearby offers.',
            buttonPositive: 'Allow',
          }
        );
        return backgroundLocation === PermissionsAndroid.RESULTS.GRANTED;
      }
      return true;
    }
    return false;
  }
};
```

---

## 6. Start Location Tracking

After user grants permission, start tracking:

```javascript
// React Native
import Radar from 'react-native-radar';

const startTracking = async () => {
  // Check permission first
  const hasPermission = await requestLocationPermission();

  if (!hasPermission) {
    console.log('Location permission not granted');
    return;
  }

  // Start tracking with responsive preset
  // Options: CONTINUOUS, RESPONSIVE, EFFICIENT
  await Radar.startTrackingResponsive();

  // Or use custom options
  await Radar.startTrackingCustom({
    desiredStoppedUpdateInterval: 180,      // 3 minutes when stopped
    desiredMovingUpdateInterval: 60,         // 1 minute when moving
    desiredSyncInterval: 20,                 // Sync every 20 seconds
    desiredAccuracy: 'high',
    stopDuration: 140,
    stopDistance: 70,
    sync: 'all',
    replay: 'stops',
    showBlueBar: true,                       // iOS only
    useStoppedGeofence: true,
    stoppedGeofenceRadius: 100,
    useMovingGeofence: true,
    movingGeofenceRadius: 100,
    syncGeofences: true,
    useVisits: true,
    useSignificantLocationChanges: true,
    beacons: false,
  });
};
```

### Tracking Presets Explained

| Preset | Battery Impact | Update Frequency | Best For |
|--------|---------------|------------------|----------|
| `CONTINUOUS` | High | Every few seconds | Real-time tracking apps |
| `RESPONSIVE` | Medium | Every 1-2 minutes | **Recommended for Trendpin** |
| `EFFICIENT` | Low | Every 5-10 minutes | Battery-sensitive apps |

---

## 7. Setup Push Notifications (FCM)

### Store FCM Token in Backend

When user logs in or token refreshes, send it to your backend:

```javascript
// React Native with Firebase
import messaging from '@react-native-firebase/messaging';
import api from './api'; // Your API client

const setupPushNotifications = async () => {
  // Request permission
  const authStatus = await messaging().requestPermission();

  if (authStatus === messaging.AuthorizationStatus.AUTHORIZED) {
    // Get FCM token
    const fcmToken = await messaging().getToken();

    // Send to backend
    await api.post('/api/v1/user/fcm-token', {
      fcm_token: fcmToken,
    });

    // Listen for token refresh
    messaging().onTokenRefresh(async (newToken) => {
      await api.post('/api/v1/user/fcm-token', {
        fcm_token: newToken,
      });
    });
  }
};
```

### Backend API Endpoint Needed

Create an endpoint to store the FCM token:

```php
// Add this endpoint to your User module
Route::post('/user/fcm-token', function (Request $request) {
    $request->validate(['fcm_token' => 'required|string']);

    auth()->user()->update([
        'fcm_token' => $request->fcm_token,
    ]);

    return response()->json(['success' => true]);
})->middleware('auth:sanctum');
```

### Handle Incoming Notifications

```javascript
// React Native
import messaging from '@react-native-firebase/messaging';
import { navigate } from './navigation'; // Your navigation service

// Background/quit state handler
messaging().setBackgroundMessageHandler(async (remoteMessage) => {
  console.log('Background notification:', remoteMessage);
});

// Foreground handler
messaging().onMessage(async (remoteMessage) => {
  // Show local notification or in-app alert
  showNotification(remoteMessage);
});

// Handle notification tap
messaging().onNotificationOpenedApp((remoteMessage) => {
  const { data } = remoteMessage;

  if (data.type === 'geofence_offer') {
    navigate('OfferDetails', {
      offerId: data.offer_id,
      brandId: data.brand_id,
    });
  }
});
```

---

## 8. User Interests Integration

For the geofence notifications to work properly, users must have selected interests that match brand categories.

### Interests Selection Screen

```javascript
// React Native
import React, { useState, useEffect } from 'react';
import api from './api';

const InterestsScreen = () => {
  const [interests, setInterests] = useState([]);
  const [selected, setSelected] = useState([]);

  useEffect(() => {
    loadInterests();
  }, []);

  const loadInterests = async () => {
    const response = await api.get('/api/v1/interests');
    setInterests(response.data.data);
  };

  const saveInterests = async () => {
    await api.post('/api/v1/user/interests', {
      interest_ids: selected,
    });
  };

  return (
    // Your UI for selecting interests
  );
};
```

---

## 9. Complete Integration Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        MOBILE APP                                │
├─────────────────────────────────────────────────────────────────┤
│ 1. User logs in                                                  │
│    └── Call: Radar.setUserId(user.id)                           │
│    └── Call: Radar.setMetadata({ user_id: user.id })            │
│                                                                  │
│ 2. Request location permission                                   │
│    └── iOS: Always + Background                                  │
│    └── Android: Fine + Background                                │
│                                                                  │
│ 3. Start Radar tracking                                          │
│    └── Call: Radar.startTrackingResponsive()                    │
│                                                                  │
│ 4. Setup FCM                                                     │
│    └── Get token and send to backend                            │
│                                                                  │
│ 5. User selects interests                                        │
│    └── POST /api/v1/user/interests                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        RADAR.IO                                  │
├─────────────────────────────────────────────────────────────────┤
│ • Tracks user location in background                            │
│ • Detects geofence entry/exit                                   │
│ • Sends webhook to backend                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     TRENDPIN BACKEND                            │
├─────────────────────────────────────────────────────────────────┤
│ 1. Receive webhook at /webhooks/radar                           │
│ 2. Verify signature                                              │
│ 3. Extract user_id from event metadata                          │
│ 4. Check user interests vs brand categories                     │
│ 5. Apply throttling rules                                       │
│ 6. Find best matching offer                                     │
│ 7. Send FCM push notification                                   │
│ 8. Log notification in database                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        MOBILE APP                                │
├─────────────────────────────────────────────────────────────────┤
│ • Receives push notification                                    │
│ • User taps notification                                        │
│ • Navigate to offer details                                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. Testing

### Method 1: Admin Dashboard Simulator (Recommended)

The easiest way to test the geofence notification system is using the built-in admin simulator:

1. Navigate to `/admin/geofence/test`
2. Select a user from the dropdown
3. Choose a geofence (auto-fills coordinates and brand) or enter coordinates manually
4. Select event type (entry, exit, dwell) - only "entry" triggers notifications
5. Click "Simulate Event" to process the event through the full notification flow

#### Eligibility Checker

Before simulating, you can use "Check Eligibility" to see:
- Whether the user should receive a notification
- User's interests vs brand's categories
- Recent notifications sent to this user
- Current throttle status (daily/weekly limits, quiet hours)
- Matching offer that would be sent

This helps debug why notifications might not be sending.

### Method 2: Radar Dashboard Simulation

1. In Radar Dashboard, create a test geofence
2. Use Radar's "Simulate" feature to trigger events
3. Or use the Radar SDK to manually track:

```javascript
// Manually track a location for testing
await Radar.trackOnce({
  latitude: 25.2048,  // Your test geofence location
  longitude: 55.2708,
});
```

### Method 3: Verify Webhook Received

Check the admin dashboard at `/admin/geofence/notifications` to see if events are being received.

### Debug Checklist

- [ ] Radar SDK initialized with correct publishable key
- [ ] User ID set after login (`Radar.setUserId`)
- [ ] Metadata includes `user_id` field
- [ ] Location permissions granted (including background)
- [ ] Tracking started (`Radar.startTrackingResponsive`)
- [ ] FCM token stored in backend
- [ ] User has selected interests
- [ ] Geofences exist and are synced to Radar
- [ ] Webhook URL is correct in Radar Dashboard
- [ ] Not in quiet hours

---

## 11. Troubleshooting

### No Notifications Received

1. **Check Radar Dashboard** - Are events being triggered?
2. **Check Backend Logs** - Is webhook being received?
3. **Check Admin Dashboard** - Are notifications being logged?
4. **Verify User ID** - Is `user_id` in event metadata?

### User ID Not in Webhook

Make sure you call `Radar.setMetadata({ user_id: user.id })` after login.

### Throttled Notifications

Check the throttle settings in admin dashboard:
- Daily limit not exceeded
- Not in quiet hours
- Brand/location cooldown not active

### Location Not Updating

1. Check battery optimization settings
2. Ensure background location permission
3. Try `CONTINUOUS` tracking for testing

---

## 12. API Endpoints Summary

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/interests` | GET | Get all interests |
| `/api/v1/user/interests` | POST | Set user interests |
| `/api/v1/user/fcm-token` | POST | Store FCM token |
| `/webhooks/radar` | POST | Radar webhook (internal) |

---

## Need Help?

- [Radar.io Documentation](https://radar.io/documentation)
- [Radar SDK Reference](https://radar.io/documentation/sdk)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
