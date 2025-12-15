# Brand API Documentation

## Endpoints

### Get All Brands
```
GET /api/v1/brands
```

### Get Brand by ID
```
GET /api/v1/brands/{id}
```

### Get Brand by Slug
```
GET /api/v1/brands/slug/{slug}
```

---

## Filter Parameters

### Search
Search brands by name, title, or description.

| Parameter | Type | Example |
|-----------|------|---------|
| `search` | string | `?search=coffee` |

---

### Categories
Filter brands by one or multiple category IDs.

| Parameter | Type | Format | Example |
|-----------|------|--------|---------|
| `category_ids` | array | Comma-separated | `?category_ids=14,15,16` |
| `category_ids[]` | array | Array format | `?category_ids[]=14&category_ids[]=15` |

> Returns brands that belong to **any** of the specified categories (OR logic).

---

### Featured
Filter by featured status.

| Parameter | Type | Values | Example |
|-----------|------|--------|---------|
| `featured` | boolean | `true`, `false` | `?featured=true` |

---

### Offer Type
Filter brands by their active offer discount type.

| Parameter | Type | Values | Example |
|-----------|------|--------|---------|
| `offer_type` | string | `bogo`, `percentage`, `fixed` | `?offer_type=bogo` |

| Value | Description |
|-------|-------------|
| `bogo` | Buy One Get One offers |
| `percentage` | Percentage discount offers |
| `fixed` | Fixed amount discount offers |

---

### Has Active Offers
Filter brands that have active offers.

| Parameter | Type | Values | Example |
|-----------|------|--------|---------|
| `has_offers` | boolean | `true`, `false` | `?has_offers=true` |

---

### Location (Proximity)
Filter brands by distance from a location using the Haversine formula.

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `lat` | float | Yes | Latitude coordinate | `?lat=31.9539` |
| `lng` | float | Yes | Longitude coordinate | `?lng=35.9106` |
| `radius` | integer | No | Search radius in km (default: 10) | `?radius=5` |

**Example:**
```
?lat=31.9539&lng=35.9106&radius=10
```

> When location filter is applied, the response includes a `distance` field (in km) for each brand.

---

## Sorting

| Parameter | Type | Values | Default |
|-----------|------|--------|---------|
| `sort_by` | string | `created_at`, `name`, `title`, `featured`, `distance` | `created_at` |
| `sort_order` | string | `asc`, `desc` | `desc` |

**Example:**
```
?sort_by=name&sort_order=asc
```

> `distance` sorting only works when location filter (`lat`, `lng`) is applied.

---

## Pagination

| Parameter | Type | Range | Default |
|-----------|------|-------|---------|
| `per_page` | integer | 1-50 | 15 |

**Example:**
```
?per_page=20
```

---

## Response Format

### Success Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Brand Name",
            "title": "Brand Title",
            "title_ar": "عنوان العلامة",
            "slug": "brand-slug",
            "description": "Brand description",
            "description_ar": "وصف العلامة",
            "logo": "http://example.com/logo.png",
            "featured_image": "http://example.com/featured.png",
            "gallery": [
                {
                    "id": 1,
                    "url": "http://example.com/image.png",
                    "thumbnail_url": "http://example.com/thumb.png",
                    "medium": "http://example.com/medium.png",
                    "large": "http://example.com/large.png"
                }
            ],
            "phone_number": "+962790000000",
            "website_link": "https://example.com",
            "insta_link": "https://instagram.com/brand",
            "facebook_link": "https://facebook.com/brand",
            "location": "Amman, Jordan",
            "lat": "31.9539",
            "lng": "35.9106",
            "distance": 2.5,
            "status": "publish",
            "open_status": 1,
            "days": null,
            "featured": true,
            "is_wishlisted": false,
            "categories": [
                {
                    "id": 14,
                    "name": "Category Name",
                    "name_ar": "اسم التصنيف"
                }
            ],
            "branches_count": 3,
            "branches": [],
            "active_offers_count": 2,
            "offers": [],
            "created_at": "2025-12-15T12:00:00+00:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 15,
        "total": 150
    }
}
```

---

## Combined Filter Examples

### Get featured brands with active offers in specific categories
```
GET /api/v1/brands?featured=true&has_offers=true&category_ids=14,15
```

### Get nearby brands with BOGO offers sorted by distance
```
GET /api/v1/brands?lat=31.9539&lng=35.9106&radius=5&offer_type=bogo&sort_by=distance&sort_order=asc
```

### Search for brands with pagination
```
GET /api/v1/brands?search=coffee&per_page=10&sort_by=name&sort_order=asc
```
