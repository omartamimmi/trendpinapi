# Wishlist Module

User favorites and wishlist functionality for TrendPin.

## Overview

The Wishlist module provides wishlist/favorites functionality using the LamaLama Wishlist package, allowing users to save their favorite shops and brands.

## Architecture

```
Wishlist/
├── Providers/
│   ├── WishlistServiceProvider.php
│   └── RouteServiceProvider.php
└── routes/
    ├── api.php
    └── web.php
```

## Implementation

This module is a thin wrapper around the `LamaLama/Wishlist` package. The actual wishlist functionality is provided by:

- **Wishlistable Trait** - Added to Shop and Brand models
- **User Module** - WishlistController handles API endpoints

## Models with Wishlist Support

### Shop
```php
use LamaLama\Wishlist\Wishlistable;

class Shop extends Model
{
    use Wishlistable;
    // ...
}
```

### Brand
```php
use LamaLama\Wishlist\Wishlistable;

class Brand extends Model
{
    use Wishlistable;
    // ...
}
```

## API Endpoints

Handled by `Modules\User\Http\Controllers\WishlistController`:

### Add to Wishlist
```
POST /api/v1/add-to-wishlist
{
    "model_type": "shop",
    "model_id": 123
}
```

### Remove from Wishlist
```
POST /api/v1/remove-from-wishlist
{
    "model_type": "shop",
    "model_id": 123
}
```

### Get User's Wishlist
```
GET /api/v1/get-user-wishlist
```

## Usage

### In Models
```php
// Check if item is wishlisted
$shop->isWished();     // Returns wishlist record or null
$shop->isWishList();   // Returns '-solid' for UI class or ''
```

### In Controllers
```php
// Add to wishlist
$user->wish($shop);

// Remove from wishlist
$user->unwish($shop);

// Get all wishlisted items
$user->wishlists;
```

## Database

Uses the `wishlist` table created by the LamaLama package:

```sql
- id: bigint (PK)
- user_id: bigint (FK)
- model_type: string
- model_id: bigint
- created_at, updated_at
```

## Dependencies

- `lamalama/laravel-wishlist` - Core wishlist functionality
- Used by `Modules\User` for API endpoints
- Used by `Modules\Shop` and `Modules\Business` for wishlistable trait
