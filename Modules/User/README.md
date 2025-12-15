# User Module

User authentication, profile management, and customer features.

## Overview

The User module handles all user-related functionality including:
- Authentication (login, register, social login)
- Profile management
- Wishlists
- User notification preferences

## Architecture

```
User/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php         - Authentication
│   │   │   ├── CustomerController.php     - Customer features
│   │   │   ├── WishlistController.php     - Wishlist management
│   │   │   └── NotificationController.php - Notification prefs
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       ├── RegisterRequest.php
│   │       └── ...
├── Services/
│   ├── AuthService.php      - Authentication logic
│   ├── UserService.php      - User operations
│   ├── LogoutUserService.php
│   └── OtpAuthService.php   - OTP authentication
├── Repositories/
│   └── UserRepository.php
└── routes/
    └── api.php
```

## Authentication

### Standard Auth
- Email/password registration
- Email/password login
- Password reset flow

### Social Login
- Google OAuth
- Facebook OAuth
- Apple Sign In

### API Tokens
Uses Laravel Sanctum for API token authentication.

## API Endpoints

### Authentication (`/api/v1/`)

```
POST /register          - Register new user
POST /login             - Login with credentials
POST /logout            - Logout (auth required)
GET  /login/{provider}  - Social login redirect
POST /socialLogin       - Mobile social login
```

### Profile (`/api/v1/`) - Auth Required

```
GET  /get-user-profile     - Get current user
POST /update-user-profile  - Update profile
POST /change-password      - Change password
POST /destroy              - Delete account
POST /save-token           - Save FCM token
```

### Wishlist (`/api/v1/`) - Auth Required

```
POST /add-to-wishlist         - Add shop to wishlist
POST /remove-from-wishlist    - Remove from wishlist
GET  /get-user-wishlist       - Get user's wishlist
```

### Notification Preferences (`/api/v1/`) - Auth Required

```
GET /set-user-interest-to-shop - Toggle shop notifications
```

## Services

### AuthService
Handles authentication logic:
- User registration with role assignment
- Login validation
- Social authentication processing
- Token management

### UserService
User-related operations:
- Profile updates
- Password changes
- FCM token management
- Shop notification preferences

```php
$userService
    ->setInputs($request->validated())
    ->setAuthUser($user)
    ->updateUserProfile()
    ->collectOutput('user', $updatedUser);
```

## Models

Uses `App\Models\User` with extensions:
- Spatie roles/permissions
- Retailer onboarding relationship
- Wishlist functionality

## Request Validation

Form requests handle validation:
- `LoginRequest` - Email, password
- `RegisterRequest` - Name, email, password, phone
- `UserInterestNotificationRequest` - Shop ID, status

## Dependencies

- `Laravel Sanctum` - API authentication
- `Spatie Permission` - Role management
- `LamaLama Wishlist` - Wishlist functionality
- `Modules\Otp` - Phone verification
