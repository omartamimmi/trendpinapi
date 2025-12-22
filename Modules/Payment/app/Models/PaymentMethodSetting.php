<?php

namespace Modules\Payment\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethodSetting extends Model
{
    protected $fillable = [
        'method',
        'display_name',
        'display_name_ar',
        'is_enabled',
        'preferred_gateway',
        'sort_order',
        'icon',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Scope: Enabled methods
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Get all enabled payment methods
     */
    public static function getEnabledMethods(): \Illuminate\Database\Eloquent\Collection
    {
        return self::enabled()->orderBy('sort_order')->get();
    }

    /**
     * Check if a specific method is enabled
     */
    public static function isMethodEnabled(string $method): bool
    {
        return self::where('method', $method)->where('is_enabled', true)->exists();
    }

    /**
     * Get preferred gateway for a method
     */
    public static function getPreferredGateway(string $method): ?string
    {
        $setting = self::where('method', $method)->first();
        return $setting?->preferred_gateway;
    }
}
