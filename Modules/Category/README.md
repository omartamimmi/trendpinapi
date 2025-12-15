# Category Module

Content classification system for TrendPin shops and brands.

## Overview

The Category module provides hierarchical categorization for shops and brands, supporting multi-language content (English and Arabic).

## Architecture

```
Category/
├── Http/
│   ├── Controllers/
│   │   ├── CategoryController.php       - API endpoints
│   │   └── Admin/CategoriesController.php
│   └── Requests/
├── Models/
│   └── Category.php
├── Repositories/
│   └── CategoryRepository.php
├── Services/
│   ├── CategoryService.php
│   ├── AdminCategoryService.php
│   └── FrontendCategoryService.php
└── routes/
    ├── api.php
    └── web.php
```

## Model

### Category

**Fillable Fields:**
- `name`, `name_ar` - Category names
- `description`, `description_ar` - Descriptions
- `slug` - URL-friendly identifier
- `status` - draft, published
- `image_id` - Featured image
- `create_user`, `update_user` - Audit fields

**Features:**
- Automatic slug generation
- Featured image support
- Soft deletes

## API Endpoints

### Public
- `GET /api/v1/categories` - List published categories
- `GET /api/v1/categories/{slug}` - Get category by slug

### Admin
- `GET /admin/categories` - List all categories
- `POST /admin/categories` - Create category
- `PUT /admin/categories/{id}` - Update category
- `DELETE /admin/categories/{id}` - Delete category

## Service Layer

```php
$categoryService
    ->setInputs($request->validated())
    ->createCategory()
    ->collectOutput('category', $category);
```

## Relationships

- **Shops** - Categories can have many shops
- **Brands** - Categories can have many brands
- **Tags** - Categories can be associated with tags
- **Interests** - Categories can be linked to interests

## Dependencies

- `Modules\Media` - For image handling
