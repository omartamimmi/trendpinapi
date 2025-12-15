<?php

namespace Modules\Tag\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Category\app\Models\Category;
use Modules\Media\Helpers\FileHelper;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'name_ar',
        'description_ar',
        'status',
        'image_id',
        'featured_image',
        'publish_date',
        'create_user',
        'update_user'
    ];

    protected $slugField = 'slug';
    protected $slugFromField = 'name';

    public function save(array $options = [])
    {
        $this->featured_image = $this->getFeaturedImage()[0]['featured_mobile'] ?? $this->featured_image;

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

    public function getFeaturedImage()
    {
        if (empty($this->image_id))
            return $this->image_id;
        $list_item = [];
        if ($this->image_id) {
            $list_item[] = [
                'featured_mobile' => FileHelper::url($this->image_id, 'cat_image'),
                'featured_web' => FileHelper::url($this->image_id, 'thumb'),
            ];
        }
        return $list_item;
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

    public function categories()
    {
        return $this->belongsToMany(Category::class,
            "tag_has_categories",
            "tag_id",
            "category_id")->withPivot('category_id');
    }

    public function brands()
    {
        return $this->belongsToMany(\Modules\Business\app\Models\Brand::class,
            "brand_has_tags",
            "tag_id",
            "brand_id");
    }
}
