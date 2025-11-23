<?php

namespace Modules\RetailerOnboarding\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'brand_id',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'max_claims',
        'claims_count',
        'views_count',
        'terms',
        'branch_ids',
        'all_branches',
        'status',
    ];

    protected $casts = [
        'branch_ids' => 'array',
        'all_branches' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getBranches()
    {
        if ($this->all_branches && $this->brand_id) {
            return Branch::where('brand_id', $this->brand_id)->get();
        }

        if ($this->branch_ids) {
            return Branch::whereIn('id', $this->branch_ids)->get();
        }

        return collect();
    }

    public function isActive()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ($this->max_claims && $this->claims_count >= $this->max_claims) {
            return false;
        }

        return true;
    }
}
