<?php
namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Modules\Media\Database\factories\MediaFileFactory;

class MediaFile extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'media_files';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'file_extension',
        'file_width',
        'file_height',
        'create_user'
    ];

    protected $appends = ['url', 'thumbnail_url'];

    protected static function newFactory()
    {
        return MediaFileFactory::new();
    }

    public static function findMediaByName($name)
    {
        return MediaFile::where("file_name", $name)->firstOrFail();
    }

    public function cacheKey()
    {
        return sprintf("%s/%s", $this->getTable(), $this->getKey());
    }

    public function findById($id)
    {
        return Cache::rememberForever($this->cacheKey() . ':' . $id, function () use ($id) {
            return $this->find($id);
        });
    }

    public function save(array $options = [])
    {
        if ($this->create_user) {
            $this->update_user = Auth::id();
        } else {
            $this->create_user = Auth::id();
        }

        return parent::save($options);
    }

    /**
     * Get the full URL for the media file
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        return asset('storage' . $this->file_path);
    }

    /**
     * Get the thumbnail URL (150x150)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->file_name || !$this->isImage()) {
            return $this->url;
        }
        $thumbPath = 'storage/presets/150-150/' . $this->file_name;
        if (file_exists(public_path($thumbPath))) {
            return asset($thumbPath);
        }
        return $this->url;
    }

    /**
     * Check if the file is an image
     */
    public function isImage(): bool
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp'];
        return in_array($this->file_type, $imageTypes);
    }

    /**
     * Get URL for a specific preset size
     */
    public function getPresetUrl(string $size = 'medium'): ?string
    {
        $presets = [
            'thumb' => '150-150',
            'small' => '320-240',
            'medium' => '450-360',
            'large' => '688-425',
        ];

        if (!isset($presets[$size]) || !$this->isImage()) {
            return $this->url;
        }

        $presetPath = 'storage/presets/' . $presets[$size] . '/' . $this->file_name;
        if (file_exists(public_path($presetPath))) {
            return asset($presetPath);
        }
        return $this->url;
    }

    /**
     * Get the user who created this media
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'create_user');
    }

    /**
     * Pivot data for mediable relationship
     */
    public function pivot_data()
    {
        return $this->pivot ? [
            'collection' => $this->pivot->collection,
            'order' => $this->pivot->order,
            'custom_properties' => $this->pivot->custom_properties,
        ] : null;
    }
}
