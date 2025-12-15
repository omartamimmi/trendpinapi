<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Interest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'status',
        'image_id',
        'create_user',
        'update_user',
    ];

    protected $slugField = 'slug';
    protected $slugFromField = 'name';

    public function save(array $options = [])
    {
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

    public function categories()
    {
        return $this->belongsToMany(
            \Modules\Category\Models\Category::class,
            'category_has_interests',
            'interest_id',
            'category_id'
        );
    }
}
