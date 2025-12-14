<?php

namespace Modules\Notification\app\Contracts;

use Modules\Notification\app\DTOs\ChannelCredentials;

/**
 * Interface for credential storage repository
 * Implements the Repository Pattern for data access
 */
interface CredentialRepositoryInterface
{
    /**
     * Get credentials for a specific channel
     */
    public function getByChannel(string $channel): ?ChannelCredentials;

    /**
     * Get all credentials
     *
     * @return ChannelCredentials[]
     */
    public function getAll(): array;

    /**
     * Save credentials for a channel
     */
    public function save(string $channel, array $credentials): ChannelCredentials;

    /**
     * Delete credentials for a channel
     */
    public function delete(string $channel): bool;

    /**
     * Check if credentials exist for a channel
     */
    public function exists(string $channel): bool;

    /**
     * Get the status of all channels
     *
     * @return array<string, string>
     */
    public function getAllStatuses(): array;

    /**
     * Update test result for a channel
     */
    public function updateTestResult(string $channel, bool $success, ?string $message = null): void;

    /**
     * Set channel active status
     */
    public function setActive(string $channel, bool $active): void;
}
