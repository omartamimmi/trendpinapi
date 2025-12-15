# Business Module

Brand and Branch management for TrendPin retailers.

## Overview

The Business module handles the core business entities: Brands and Branches. Each retailer can have multiple brands, and each brand can have multiple branch locations.

## Architecture

```
Business/
├── app/
│   ├── Http/Controllers/    - (Empty - routes handled by Admin/RetailerOnboarding)
│   └── Models/
│       ├── Brand.php        - Main brand model
│       ├── BrandMeta.php    - Brand metadata (hours, discounts)
│       └── Branch.php       - Branch locations
├── Repositories/
│   ├── Contracts/
│   │   ├── BrandRepositoryInterface.php
│   │   ├── BrandMetaRepositoryInterface.php
│   │   └── BranchRepositoryInterface.php
│   ├── BrandRepository.php
│   ├── BrandMetaRepository.php
│   └── BranchRepository.php
├── Services/
│   └── BrandService.php     - Brand business logic
└── database/
    └── migrations/
```

## Models

### Brand
Primary entity representing a retailer's business/store.

**Fillable Fields:**
- `name`, `title`, `title_ar` - Brand names
- `description`, `description_ar` - Descriptions
- `logo`, `gallery` - Media
- `phone_number`, `location` - Contact info
- `lat`, `lng` - Geolocation
- `status` - Publication status
- `create_user` - Owner (retailer) ID

**Relationships:**
- `branches()` - HasMany Branch
- `categories()` - BelongsToMany Category
- `tags()` - BelongsToMany Tag
- `meta()` - HasOne BrandMeta

### Branch
Physical location/store of a brand.

**Fillable Fields:**
- `brand_id` - Parent brand
- `name` - Branch name
- `location`, `lat`, `lng` - Location data

### BrandMeta
Extended brand information.

**Fields:**
- `enable_open_hours`, `open_hours` - Operating hours
- `enable_discount`, `discount_type`, `discount` - Discount info

## Repository Pattern

The module implements the Repository pattern for data access:

```php
// Example usage
$brandRepository->getAllBrandsByAuthor($userId);
$brandRepository->getBrandById($id);
$brandRepository->create($data);
$brandRepository->update($id, $data);
$brandRepository->deleteBrand($id);
```

## Service Layer

`BrandService` provides business logic with fluent interface:

```php
$brandService
    ->setInputs($request->validated())
    ->createBrand()
    ->storeMetaData()
    ->syncCategory()
    ->syncTag()
    ->collectOutput('brand', $brand);
```

## Routes

Brand routes are handled by:
- `Modules\Admin` - Admin brand management
- `Modules\RetailerOnboarding` - Retailer brand management

## Dependencies

- `Modules\Category` - For category relationships
- `Modules\Tag` - For tag relationships
- `Modules\Media` - For image handling
