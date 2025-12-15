# Location Module

Geographic location management for TrendPin.

## Overview

The Location module manages geographic locations for shops and brands, supporting geolocation features and location-based discovery.

## Architecture

```
Location/
├── Http/
│   ├── Controllers/
│   │   ├── LocationController.php
│   │   └── AdminControllers/LocationController.php
├── Models/
│   └── Location.php
├── Repositories/
│   └── LocationRepository.php
├── Services/
│   └── LocationService.php
├── Policies/
│   └── LocationPolicy.php
└── routes/
    ├── api.php
    ├── web.php
    └── admin.php
```

## Model

### Location

**Fillable Fields:**
- `name`, `name_ar` - Location names
- `description` - Description
- `slug` - URL-friendly identifier
- `lat`, `lng` - Geolocation coordinates
- `status` - Publication status
- `create_user`, `update_user` - Audit fields

**Features:**
- Automatic slug generation
- Coordinate storage for geolocation
- Soft deletes

## API Endpoints

### Public
- `GET /api/v1/locations` - List locations
- `GET /api/v1/locations/{slug}` - Get location by slug

### Admin
- `GET /admin/locations` - List all locations
- `POST /admin/locations` - Create location
- `PUT /admin/locations/{id}` - Update location
- `DELETE /admin/locations/{id}` - Delete location

## Geolocation Features

The module supports:
- Coordinate-based location storage
- Distance calculations using Haversine formula
- Location-based filtering for nearby content

## Authorization

`LocationPolicy` controls access:
- `view` - Anyone
- `create` - Admin only
- `update` - Admin only
- `delete` - Admin only

## Dependencies

- Used by `Modules\Shop` for shop locations
- Used by `Modules\Notification` for location-based targeting
