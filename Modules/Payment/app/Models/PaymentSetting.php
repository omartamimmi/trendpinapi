<?php

namespace Modules\Payment\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class PaymentSetting extends Model
{
    protected $fillable = [
        'gateway',
        'display_name',
        'display_name_ar',
        'description',
        'description_ar',
        'is_enabled',
        'is_sandbox',
        'credentials',
        'supported_methods',
        'icon',
        'logo_id',
        'sort_order',
        'fee_percentage',
        'fee_fixed',
        'min_amount',
        'max_amount',
        'webhook_url',
        'webhook_secret',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_sandbox' => 'boolean',
        'supported_methods' => 'array',
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
    ];

    protected $hidden = [
        'credentials',
        'webhook_secret',
    ];

    /**
     * Get decrypted credentials
     */
    public function getDecryptedCredentials(): array
    {
        if (empty($this->credentials)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($this->credentials), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set encrypted credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = Crypt::encryptString(json_encode($credentials));
    }

    /**
     * Get specific credential
     */
    public function getCredential(string $key, $default = null)
    {
        $credentials = $this->getDecryptedCredentials();
        return $credentials[$key] ?? $default;
    }

    /**
     * User who last updated
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Enabled gateways
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: By gateway name
     */
    public function scopeGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Check if gateway supports a method
     */
    public function supportsMethod(string $method): bool
    {
        return in_array($method, $this->supported_methods ?? []);
    }

    /**
     * Get setting for a specific gateway
     */
    public static function forGateway(string $gateway): ?self
    {
        return self::where('gateway', $gateway)->first();
    }

    /**
     * Get all enabled gateways
     */
    public static function getEnabledGateways(): \Illuminate\Database\Eloquent\Collection
    {
        return self::enabled()->orderBy('sort_order')->get();
    }

    /**
     * Check if any gateway is enabled
     */
    public static function hasEnabledGateway(): bool
    {
        return self::enabled()->exists();
    }

    /**
     * Get a setting value (for 'general' gateway)
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getDecryptedCredentials();
        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value (for 'general' gateway)
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->getDecryptedCredentials();
        $settings[$key] = $value;
        $this->setCredentials($settings);
    }

    /**
     * Set multiple settings at once
     */
    public function setSettings(array $values): void
    {
        $settings = $this->getDecryptedCredentials();
        foreach ($values as $key => $value) {
            $settings[$key] = $value;
        }
        $this->setCredentials($settings);
    }
}
