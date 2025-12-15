# Shop Module

Shop/Product management with locations, categories, and availability.

## Overview

The Shop module manages retail shop listings including:
- Shop information and media
- Location and geolocation
- Categories and tags
- Operating hours and discounts
- Wishlist integration

## Architecture

```
Shop/
├── Http/
│   ├── Controllers/
│   │   ├── AdminShopController.php  - Admin management
│   │   ├── Api/ShopController.php   - API endpoints
│   │   └── ShopController.php       - Web routes
│   └── Requests/
│       ├── StoreShopRequest.php
│       └── ...
├── Models/
│   ├── Shop.php      - Main shop model
│   └── ShopMeta.php  - Extended metadata
├── Repositories/
│   ├── ShopRepository.php
│   └── ShopMetaRepository.php
├── Services/
│   ├── ShopService.php         - Core business logic
│   ├── AdminShopService.php    - Admin operations
│   └── FrontendShopService.php - Customer operations
├── Transformers/
│   ├── ShopResource.php
│   ├── ShopCollection.php
│   └── ShopFeaturedCollection.php
├── Policies/
│   └── ShopPolicy.php
└── routes/
    ├── api.php
    └── web.php
```

## Models

### Shop
Main shop entity.

**Fillable Fields:**
- `title`, `description` - Basic info
- `title_ar`, `description_ar` - Arabic translations
- `status` - draft, publish
- `image_id`, `gallery`, `video` - Media
- `location_id`, `lat`, `lng` - Location
- `days`, `open_status` - Availability
- `featured`, `featured_mobile` - Featured flags
- `create_user` - Owner ID

**Relationships:**
- `location()` - BelongsTo Location
- `meta()` - HasOne ShopMeta
- `categories()` - BelongsToMany Category
- `tags()` - BelongsToMany Tag

### ShopMeta
Extended shop information.

**Fields:**
- `enable_open_hours`, `open_hours`
- `enable_discount`, `discount_type`, `discount`
- Various metadata fields

## Repository Pattern

```php
$shopRepository->getAllShops();
$shopRepository->getShopById($id);
$shopRepository->getShopsByAuthor($userId);
$shopRepository->create($data);
$shopRepository->update($id, $data);
```

## Service Layer

`ShopService` with fluent interface:

```php
$shopService
    ->setInputs($request->validated())
    ->setInput('authId', Auth::id())
    ->createShop()
    ->storeMetaData()
    ->updateExactLocation()
    ->syncCategory()
    ->syncTag()
    ->collectOutput('shop', $shop);
```

## API Endpoints

### Public
- `GET /api/v1/shops` - List shops
- `GET /api/v1/shops/{id}` - Get shop details
- `GET /api/v1/shops/featured` - Featured shops
- `GET /api/v1/shops/nearby` - Shops near location

### Protected (Auth Required)
- `POST /api/v1/shops` - Create shop
- `PUT /api/v1/shops/{id}` - Update shop
- `DELETE /api/v1/shops/{id}` - Delete shop
- `GET /api/v1/my-shops` - User's shops
- `POST /api/v1/shops/{id}/status` - Update status

## Transformers

API responses use Laravel Resources:

```php
return new ShopResource($shop);
return new ShopCollection($shops);
return new ShopFeaturedCollection($featured);
```

## Authorization

`ShopPolicy` controls access:
- `view` - Anyone can view published shops
- `create` - Authenticated users
- `update` - Shop owner only
- `delete` - Shop owner only

## Dependencies

- `Modules\Category` - Category relationships
- `Modules\Tag` - Tag relationships
- `Modules\Location` - Location model
- `Modules\Media` - Image handling
- `LamaLama Wishlist` - Wishlist trait
