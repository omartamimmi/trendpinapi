# Media Module

File upload and image processing for TrendPin.

## Overview

The Media module handles all file uploads, image processing, and media asset management including:
- Image uploads with multiple preset sizes
- Gallery management
- HEIC to WebP conversion
- Image optimization

## Architecture

```
Media/
├── Http/
│   └── Controllers/
│       └── MediaController.php
├── Models/
│   └── MediaFile.php
├── Repositories/
│   └── MediaRepository.php
├── Services/
│   └── MediaService.php
├── Helpers/
│   ├── FileHelper.php     - URL generation
│   └── ResizeImage.php    - Image processing
├── Requests/
│   ├── MediaRequest.php
│   ├── AllMediaRequest.php
│   └── PrivateFileRequest.php
└── routes/
    ├── api.php
    └── web.php
```

## Model

### MediaFile

**Fillable Fields:**
- `file_name` - Original filename
- `file_path` - Storage path
- `file_type` - MIME type
- `file_size` - Size in bytes
- `file_extension` - File extension
- `create_user`, `update_user` - Audit fields

## Image Presets

The module generates multiple sizes for uploaded images:

| Preset | Size | Use Case |
|--------|------|----------|
| thumb | 95x95 | Thumbnails |
| small | 100x100 | Icons |
| medium | 320px | Mobile |
| large | 450px | Tablet |
| full | Original | Desktop |
| cat_image | Category images | Categories |

## API Endpoints

### Upload
- `POST /api/v1/media/upload` - Upload single file
- `POST /api/v1/media/upload-multiple` - Upload multiple files

### Retrieve
- `GET /api/v1/media/{id}` - Get media file info
- `GET /api/v1/media` - List user's media files

### Delete
- `DELETE /api/v1/media/{id}` - Delete media file

## Service Layer

```php
$mediaService
    ->setInputs($request->validated())
    ->uploadImage()
    ->processImage()
    ->collectOutput('media', $mediaFile);
```

## FileHelper

Static helper for URL generation:

```php
use Modules\Media\Helpers\FileHelper;

// Get image URL by size
$thumbUrl = FileHelper::url($mediaId, 'thumb');
$fullUrl = FileHelper::url($mediaId, 'full');
```

## Image Processing

### HEIC Support
The module converts HEIC/HEIF images to WebP format using Imagick.

### Optimization
- WebP conversion for smaller file sizes
- Automatic resizing for preset sizes
- Quality optimization

## Storage

Files are stored in Laravel's storage system:
- `storage/app/public/uploads/` - Public uploads
- `storage/app/private/` - Private files

## Dependencies

- `Imagick` - Image processing
- Laravel Storage - File management
