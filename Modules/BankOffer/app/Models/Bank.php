<?php

namespace Modules\BankOffer\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Media\Models\MediaFile;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'logo_id',
        'description',
        'description_ar',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Card types belonging to this bank
     */
    public function cardTypes(): HasMany
    {
        return $this->hasMany(CardType::class);
    }

    /**
     * Bank offers created by this bank
     */
    public function offers(): HasMany
    {
        return $this->hasMany(BankOffer::class);
    }

    /**
     * Active offers only
     */
    public function activeOffers(): HasMany
    {
        return $this->hasMany(BankOffer::class)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Logo relationship
     */
    public function logo()
    {
        return $this->belongsTo(MediaFile::class, 'logo_id');
    }

    /**
     * Scope: Active banks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if bank is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
