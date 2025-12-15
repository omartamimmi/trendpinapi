<?php

namespace Modules\Notification\app\DTOs;

/**
 * Data Transfer Object for channel credentials
 */
class ChannelCredentials
{
    public function __construct(
        public readonly string $channel,
        public readonly string $provider,
        public readonly array $credentials,
        public readonly bool $isActive = false,
        public readonly ?string $lastTestedAt = null,
        public readonly ?string $lastTestResult = null,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            channel: $data['channel'] ?? '',
            provider: $data['provider'] ?? '',
            credentials: $data['credentials'] ?? [],
            isActive: $data['is_active'] ?? false,
            lastTestedAt: $data['last_tested_at'] ?? null,
            lastTestResult: $data['last_test_result'] ?? null,
        );
    }

    /**
     * Convert to array (with sensitive data masked)
     */
    public function toArray(bool $maskSensitive = true): array
    {
        $credentials = $this->credentials;

        if ($maskSensitive) {
            $sensitiveKeys = ['password', 'auth_token', 'access_token', 'server_key', 'service_account_json', 'api_secret'];
            foreach ($sensitiveKeys as $key) {
                if (isset($credentials[$key]) && !empty($credentials[$key])) {
                    $credentials[$key] = '********';
                }
            }
        }

        return [
            'channel' => $this->channel,
            'provider' => $this->provider,
            'credentials' => $credentials,
            'is_active' => $this->isActive,
            'last_tested_at' => $this->lastTestedAt,
            'last_test_result' => $this->lastTestResult,
        ];
    }

    /**
     * Get a specific credential value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Check if credentials are complete for the provider
     */
    public function isComplete(): bool
    {
        $requiredFields = $this->getRequiredFields();

        foreach ($requiredFields as $field) {
            if (empty($this->credentials[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get required fields based on channel and provider
     */
    private function getRequiredFields(): array
    {
        return match ($this->channel) {
            'smtp' => ['host', 'port', 'username', 'password', 'from_address'],
            'sms' => match ($this->provider) {
                'twilio' => ['account_sid', 'auth_token', 'from_number'],
                'nexmo', 'messagebird' => ['api_key', 'api_secret', 'from_number'],
                default => [],
            },
            'whatsapp' => match ($this->provider) {
                'twilio' => ['account_sid', 'auth_token', 'from_number'],
                'meta' => ['business_id', 'phone_number_id', 'access_token'],
                default => [],
            },
            'push' => match ($this->provider) {
                'firebase' => ['project_id', 'server_key'],
                default => [],
            },
            default => [],
        };
    }
}
