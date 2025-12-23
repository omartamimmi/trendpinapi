<?php

namespace Modules\Notification\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationCredential extends Model
{
    use HasFactory;

    protected $table = 'notification_credentials';

    protected $fillable = [
        'channel',
        'provider',
        'credentials',
        'is_active',
        'last_tested_at',
        'last_test_result',
        'last_test_message',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials', // Hide by default for security
    ];

    /**
     * Get credentials (decrypted)
     */
    public function getDecryptedCredentials(): array
    {
        return $this->credentials ?? [];
    }

    /**
     * Check if channel is properly configured
     */
    public function isConfigured(): bool
    {
        if (!$this->is_active || empty($this->credentials)) {
            return false;
        }

        $requiredFields = $this->getRequiredFields();

        foreach ($requiredFields as $field) {
            if (empty($this->credentials[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get required fields for the channel/provider
     */
    private function getRequiredFields(): array
    {
        return match ($this->channel) {
            'smtp', 'email' => ['host', 'port', 'username', 'password', 'from_address'],
            'sms' => match ($this->provider) {
                'twilio' => ['account_sid', 'auth_token', 'from_number'],
                default => [],
            },
            'whatsapp' => match ($this->provider) {
                'twilio' => ['account_sid', 'auth_token', 'from_number'],
                'meta' => ['phone_number_id', 'access_token'],
                default => [],
            },
            'push' => match ($this->provider) {
                'firebase' => ['project_id', 'service_account_json'],
                default => [],
            },
            default => [],
        };
    }

    /**
     * Scope: Active credentials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By channel
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}
