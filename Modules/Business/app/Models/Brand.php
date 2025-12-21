<?php

namespace Modules\Business\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use LamaLama\Wishlist\Wishlistable;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Traits\HasMedia;
use Modules\BankOffer\app\Models\BankOfferBrand;

class Brand extends Model
{
    use HasFactory, Wishlistable, SoftDeletes, HasMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'logo',
        'location',
        'gallery',
        'website_link',
        'insta_link',
        'facebook_link',
        'create_user',
        'update_user',
        // Shop fields
        'title',
        'slug',
        'description',
        'description_ar',
        'title_ar',
        'image_id',
        'video',
        'featured_mobile',
        'status',
        'publish_date',
        'days',
        'open_status',
        'featured',
        'location_id',
        'phone_number',
        'lat',
        'lng',
        'is_main_branch',
        'main_branch_id',
        'type',
        'source_id'
    ];

    protected $slugField = 'slug';
    protected $slugFromField = 'title';

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class,
            "brand_has_categories",
            "brand_id",
            "category_id")->withPivot('category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(\Modules\Tag\app\Models\Tag::class,
            "brand_has_tags",
            "brand_id",
            "tag_id")->withPivot('tag_id');
    }

    public function meta()
    {
        return $this->hasOne(BrandMeta::class, "brand_id", 'id');
    }

    public function offers()
    {
        return $this->hasMany(\Modules\RetailerOnboarding\app\Models\Offer::class);
    }

    public function activeOffers()
    {
        return $this->hasMany(\Modules\RetailerOnboarding\app\Models\Offer::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Get all bank offer brand participations
     */
    public function bankOfferBrands()
    {
        return $this->hasMany(BankOfferBrand::class);
    }

    /**
     * Get approved bank offer participations with active offers
     */
    public function activeBankOfferBrands()
    {
        return $this->hasMany(BankOfferBrand::class)
            ->where('status', 'approved')
            ->whereHas('bankOffer', function ($query) {
                $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            });
    }

    public function save(array $options = [])
    {
        $this->featured_mobile = $this->getFeaturedImage()[0]['featured_mobile'] ?? $this->featured_mobile;
        if ($this->create_user) {
            $this->update_user = Auth::id();
        } else {
            $this->create_user = Auth::id();
        }
        if ($this->slugField && $this->slugFromField) {
            $slugField = $this->slugField;
            $this->$slugField = $this->generateSlug($this->$slugField);
        }
        return parent::save($options);
    }

    public function generateSlug($string = false, $count = 0)
    {
        $slugFromField = $this->slugFromField;
        if (empty($string))
            $string = $this->$slugFromField;
        $slug = $newSlug = $this->strToSlug($string);
        $newSlug = $slug . ($count ? '-' . $count : '');
        $model = static::select('count(id)');
        if ($this->id) {
            $model->where('id', '<>', $this->id);
        }
        $check = $model->where($this->slugField, $newSlug)->count();
        if (!empty($check)) {
            return $this->generateSlug($slug, $count + 1);
        }
        return $newSlug;
    }

    protected function strToSlug($string)
    {
        $slug = Str::slug($string);
        if (empty($slug)) {
            $slug = preg_replace('/\s+/u', '-', trim($string));
        }
        return $slug;
    }

    public function getGallery($featuredIncluded = false)
    {
        if (empty($this->gallery))
            return $this->gallery;
        $list_item = [];
        if ($featuredIncluded and $this->image_id) {
            $featuredUrl = FileHelper::url($this->image_id, 'full');
            $thumbUrl = FileHelper::url($this->image_id, 'thumb');
            if ($featuredUrl) {
                $list_item[] = [
                    'large' => str_replace(['.png', '.jpg', '.jpeg'], '.webp', $featuredUrl),
                    'thumb' => $thumbUrl ?: null
                ];
            }
        }

        $items = explode(",", $this->gallery);
        foreach ($items as $k => $item) {
            if ($item) {
                $large = FileHelper::url($item, 'full');
                $thumb = FileHelper::url($item, 'thumb');
                $medium = FileHelper::url($item, 'medium');

                // Only add if at least one URL is valid
                if ($large || $thumb || $medium) {
                    $list_item[] = [
                        'large' => $large ?: null,
                        'thumb' => $thumb ?: null,
                        'medium' => $medium ?: null
                    ];
                }
            }
        }
        return $list_item;
    }

    public function getFeaturedImage()
    {
        if (empty($this->image_id))
            return $this->image_id;
        $list_item = [];
        if ($this->image_id) {
            $list_item[] = [
                'featured_mobile' => FileHelper::url($this->image_id, 'full'),
                'featured_web' => FileHelper::url($this->image_id, 'thumb'),
            ];
        }
        return $list_item;
    }

    public function isWished()
    {
        return DB::table('wishlist')
            ->where('user_id', Auth::id())
            ->where('model_id', $this->id)
            ->first();
    }

    public function isWishList()
    {
        if (Auth::id()) {
            if (!empty($this->isWished()) and !empty($this->isWished()->id)) {
                return '-solid';
            }
        }
        return '';
    }

    /**
     * Get the logo URL using the Media module
     */
    public function getLogoUrlAttribute(): ?string
    {
        // First check for media relationship
        $logo = $this->getFirstMedia('logo');
        if ($logo) {
            return $logo->getPresetUrl('thumb');
        }

        // Fallback to legacy logo field
        if ($this->logo) {
            $url = FileHelper::url($this->logo, 'thumb');
            // FileHelper::url returns false if file not found
            return $url ?: null;
        }

        return null;
    }

    /**
     * Get the gallery images using the Media module
     */
    public function getGalleryImagesAttribute(): array
    {
        // First check for media relationship
        $galleryMedia = $this->getMedia('gallery');
        if ($galleryMedia->count() > 0) {
            return $galleryMedia->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->url,
                'thumbnail_url' => $media->thumbnail_url,
                'medium' => $media->getPresetUrl('medium'),
                'large' => $media->getPresetUrl('large'),
            ])->toArray();
        }

        // Fallback to legacy gallery field
        return $this->getGallery(true) ?: [];
    }
}
