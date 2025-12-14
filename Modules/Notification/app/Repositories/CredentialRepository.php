<?php

namespace Modules\Notification\app\Repositories;

use Modules\Notification\app\Contracts\CredentialRepositoryInterface;
use Modules\Notification\app\DTOs\ChannelCredentials;
use Modules\Notification\app\Models\NotificationCredential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Repository for notification credentials
 * Implements the Repository Pattern with encryption
 */
class CredentialRepository implements CredentialRepositoryInterface
{
    /**
     * Sensitive fields that should be encrypted
     */
    private array $sensitiveFields = [
        'password',
        'auth_token',
        'access_token',
        'server_key',
        'service_account_json',
        'api_secret',
    ];

    /**
     * Channel aliases for lookup
     */
    private array $channelAliases = [
        'email' => 'smtp',
        'smtp' => 'email',
    ];

    public function getByChannel(string $channel): ?ChannelCredentials
    {
        $credential = NotificationCredential::where('channel', $channel)->first();

        // If not found, try the alias
        if (!$credential && isset($this->channelAliases[$channel])) {
            $credential = NotificationCredential::where('channel', $this->channelAliases[$channel])->first();
        }

        if (!$credential) {
            return null;
        }

        return $this->toDTO($credential);
    }

    public function getAll(): array
    {
        $credentials = NotificationCredential::all();

        return $credentials->map(fn($c) => $this->toDTO($c))->toArray();
    }

    public function save(string $channel, array $credentials): ChannelCredentials
    {
        $provider = $credentials['provider'] ?? $this->getDefaultProvider($channel);
        unset($credentials['provider']);

        // Encrypt sensitive fields
        $encryptedCredentials = $this->encryptSensitiveFields($credentials);

        $model = NotificationCredential::updateOrCreate(
            ['channel' => $channel],
            [
                'provider' => $provider,
                'credentials' => $encryptedCredentials,
                'is_active' => true,
            ]
        );

        return $this->toDTO($model);
    }

    public function delete(string $channel): bool
    {
        return NotificationCredential::where('channel', $channel)->delete() > 0;
    }

    public function exists(string $channel): bool
    {
        return NotificationCredential::where('channel', $channel)->exists();
    }

    public function getAllStatuses(): array
    {
        $channels = ['smtp', 'sms', 'whatsapp', 'push'];
        $statuses = [];

        foreach ($channels as $channel) {
            $credential = NotificationCredential::where('channel', $channel)->first();

            if (!$credential) {
                $statuses[$channel] = 'not_configured';
            } elseif ($credential->last_test_result === 'success') {
                $statuses[$channel] = 'configured';
            } elseif ($credential->last_test_result === 'error') {
                $statuses[$channel] = 'error';
            } else {
                $statuses[$channel] = $credential->is_active ? 'configured' : 'not_configured';
            }
        }

        return $statuses;
    }

    /**
     * Update test result for a channel
     */
    public function updateTestResult(string $channel, bool $success, ?string $message = null): void
    {
        NotificationCredential::where('channel', $channel)->update([
            'last_tested_at' => now(),
            'last_test_result' => $success ? 'success' : 'error',
            'last_test_message' => $message,
        ]);
    }

    /**
     * Toggle channel active status
     */
    public function setActive(string $channel, bool $active): void
    {
        NotificationCredential::where('channel', $channel)->update([
            'is_active' => $active,
        ]);
    }

    /**
     * Convert model to DTO
     */
    private function toDTO(NotificationCredential $model): ChannelCredentials
    {
        $credentials = $this->decryptSensitiveFields($model->credentials);

        return new ChannelCredentials(
            channel: $model->channel,
            provider: $model->provider,
            credentials: $credentials,
            isActive: $model->is_active,
            lastTestedAt: $model->last_tested_at?->toIso8601String(),
            lastTestResult: $model->last_test_result,
        );
    }

    /**
     * Encrypt sensitive fields
     */
    private function encryptSensitiveFields(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // Don't re-encrypt if already encrypted (starts with eyJ)
                if (!str_starts_with($data[$field], 'eyJ')) {
                    $data[$field] = Crypt::encryptString($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * Decrypt sensitive fields
     */
    private function decryptSensitiveFields(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $data[$field] = Crypt::decryptString($data[$field]);
                } catch (DecryptException $e) {
                    // Field might not be encrypted, leave as is
                }
            }
        }

        return $data;
    }

    /**
     * Get default provider for a channel
     */
    private function getDefaultProvider(string $channel): string
    {
        return match ($channel) {
            'smtp', 'email' => 'smtp',
            'sms' => 'twilio',
            'whatsapp' => 'twilio',
            'push' => 'firebase',
            default => 'default',
        };
    }
}
