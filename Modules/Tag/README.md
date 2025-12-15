# Tag Module

Tagging system for content classification in TrendPin.

## Overview

The Tag module provides flexible tagging capabilities for shops and brands, allowing users to discover content through keyword-based navigation.

## Architecture

```
Tag/
├── app/
│   └── Models/
│       └── Tag.php
├── Http/
│   ├── Controllers/
│   │   ├── TagController.php
│   │   ├── AdminControllers/TagsController.php
│   │   └── Api/TagsController.php
│   └── Requests/
│       ├── StoreTagRequest.php
│       └── AdminDeleteTagRequest.php
├── Services/
│   ├── TagService.php
│   ├── AdminTagService.php
│   └── FrontendTagService.php
└── routes/
    ├── api.php
    ├── web.php
    └── admin.php
```

## Model

### Tag

**Fillable Fields:**
- `name`, `name_ar` - Tag names
- `description`, `description_ar` - Descriptions
- `slug` - URL-friendly identifier
- `status` - Publication status
- `image_id`, `featured_image` - Media
- `publish_date` - Publication date
- `create_user`, `update_user` - Audit fields

**Features:**
- Automatic slug generation (supports non-ASCII characters)
- Soft deletes
- Featured image support

**Relationships:**
- `categories()` - BelongsToMany Category
- `brands()` - BelongsToMany Brand

## API Endpoints

### Public
- `GET /api/v1/tags` - List all active tags
- `GET /api/v1/tags/{slug}` - Get tag by slug

### Admin
- `GET /admin/tags` - List all tags
- `POST /admin/tags` - Create tag
- `PUT /admin/tags/{id}` - Update tag
- `DELETE /admin/tags/{id}` - Delete tag

## Service Layer

```php
$tagService
    ->setInputs($request->validated())
    ->createTag()
    ->collectOutput('tag', $tag);
```

## Usage

Tags are attached to:
- **Brands** - For brand discovery
- **Shops** - For shop filtering
- **Categories** - For category grouping

## Dependencies

- `Modules\Category` - For category relationships
- `Modules\Media` - For image handling
