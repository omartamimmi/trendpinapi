<?php

namespace Modules\Notification\app\Providers;

use Modules\Notification\app\Contracts\NotificationChannelInterface;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base class for notification channels
 * Provides common functionality and template method pattern
 */
abstract class AbstractNotificationChannel implements NotificationChannelInterface
{
    protected array $config = [];
    protected string $channelType;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getChannelType(): string
    {
        return $this->channelType;
    }

    public function getConfiguration(): array
    {
        // Return config without sensitive data
        $safeConfig = $this->config;
        $sensitiveKeys = ['password', 'auth_token', 'access_token', 'server_key', 'service_account_json', 'api_secret'];

        foreach ($sensitiveKeys as $key) {
            if (isset($safeConfig[$key]) && !empty($safeConfig[$key])) {
                $safeConfig[$key] = '********';
            }
        }

        return $safeConfig;
    }

    public function setConfiguration(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    public function isConfigured(): bool
    {
        $requiredFields = $this->getRequiredConfigFields();

        foreach ($requiredFields as $field) {
            if (empty($this->config[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send batch notifications (default implementation)
     */
    public function sendBatch(array $payloads): array
    {
        $results = [];

        foreach ($payloads as $payload) {
            $results[] = $this->send($payload);
        }

        return $results;
    }

    /**
     * Get required configuration fields for this channel
     */
    abstract protected function getRequiredConfigFields(): array;

    /**
     * Log notification attempt
     */
    protected function logAttempt(NotificationPayload $payload, string $action = 'send'): void
    {
        Log::info("Notification {$action} attempt", [
            'channel' => $this->channelType,
            'recipient_type' => $payload->recipientType,
            'recipient_id' => $payload->recipientId,
        ]);
    }

    /**
     * Log notification result
     */
    protected function logResult(NotificationResult $result): void
    {
        $level = $result->success ? 'info' : 'error';

        Log::$level("Notification result", [
            'channel' => $this->channelType,
            'success' => $result->success,
            'message' => $result->message,
            'message_id' => $result->messageId,
            'error_code' => $result->errorCode,
        ]);
    }

    /**
     * Get config value with default
     */
    protected function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
