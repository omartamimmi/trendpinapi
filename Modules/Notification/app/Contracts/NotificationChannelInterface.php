<?php

namespace Modules\Notification\app\Contracts;

use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;

/**
 * Interface for all notification channel providers
 * Implements the Strategy Pattern for different notification channels
 */
interface NotificationChannelInterface
{
    /**
     * Get the channel type identifier
     */
    public function getChannelType(): string;

    /**
     * Send a notification through this channel
     */
    public function send(NotificationPayload $payload): NotificationResult;

    /**
     * Send batch notifications
     *
     * @param NotificationPayload[] $payloads
     * @return NotificationResult[]
     */
    public function sendBatch(array $payloads): array;

    /**
     * Test the channel credentials/connection
     */
    public function testConnection(): CredentialTestResult;

    /**
     * Check if the channel is properly configured
     */
    public function isConfigured(): bool;

    /**
     * Get the current configuration (without sensitive data)
     */
    public function getConfiguration(): array;

    /**
     * Set/update the configuration
     */
    public function setConfiguration(array $config): void;
}
