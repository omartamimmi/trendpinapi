<?php

namespace Modules\Notification\app\Services;

use Modules\Notification\app\Contracts\CredentialRepositoryInterface;
use Modules\Notification\app\Contracts\NotificationChannelInterface;
use Modules\Notification\app\DTOs\ChannelCredentials;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Modules\Notification\app\Factories\NotificationChannelFactory;
use Modules\Notification\app\Repositories\CredentialRepository;

/**
 * Service for managing notification credentials
 */
class CredentialService
{
    private CredentialRepositoryInterface $repository;

    public function __construct(?CredentialRepositoryInterface $repository = null)
    {
        $this->repository = $repository ?? new CredentialRepository();
    }

    /**
     * Get all credential statuses
     */
    public function getAllStatuses(): array
    {
        return $this->repository->getAllStatuses();
    }

    /**
     * Get credentials for a channel
     */
    public function getCredentials(string $channel): ?ChannelCredentials
    {
        return $this->repository->getByChannel($channel);
    }

    /**
     * Get all credentials (masked)
     */
    public function getAllCredentials(): array
    {
        $credentials = $this->repository->getAll();

        return array_map(fn($c) => $c->toArray(true), $credentials);
    }

    /**
     * Save credentials for a channel
     */
    public function saveCredentials(string $channel, array $credentials): ChannelCredentials
    {
        return $this->repository->save($channel, $credentials);
    }

    /**
     * Test channel credentials
     */
    public function testCredentials(string $channel, ?array $credentials = null): CredentialTestResult
    {
        // If credentials provided, use them; otherwise load from repository
        if ($credentials) {
            $provider = $credentials['provider'] ?? NotificationChannelFactory::getDefaultProvider($channel);
            unset($credentials['provider']);

            // Ensure provider is not null
            if (!$provider) {
                return CredentialTestResult::failure(
                    'Provider not specified',
                    'Please select a provider for this channel'
                );
            }

            $channelProvider = NotificationChannelFactory::create($channel, $provider, $credentials);
        } else {
            $storedCredentials = $this->repository->getByChannel($channel);

            if (!$storedCredentials) {
                return CredentialTestResult::failure(
                    'No credentials configured',
                    'Please configure credentials for this channel first'
                );
            }

            $channelProvider = NotificationChannelFactory::createFromCredentials($storedCredentials);
        }

        // Test the connection
        $result = $channelProvider->testConnection();

        // Update test result in repository if using stored credentials
        if (!$credentials) {
            $this->repository->updateTestResult($channel, $result->success, $result->message);
        }

        return $result;
    }

    /**
     * Get a configured channel provider
     */
    public function getChannelProvider(string $channel): ?NotificationChannelInterface
    {
        $credentials = $this->repository->getByChannel($channel);

        if (!$credentials || !$credentials->isComplete()) {
            return null;
        }

        return NotificationChannelFactory::createFromCredentials($credentials);
    }

    /**
     * Check if a channel is configured and active
     */
    public function isChannelConfigured(string $channel): bool
    {
        $credentials = $this->repository->getByChannel($channel);

        return $credentials && $credentials->isActive && $credentials->isComplete();
    }

    /**
     * Toggle channel active status
     */
    public function setChannelActive(string $channel, bool $active): void
    {
        $this->repository->setActive($channel, $active);
    }

    /**
     * Delete channel credentials
     */
    public function deleteCredentials(string $channel): bool
    {
        return $this->repository->delete($channel);
    }

    /**
     * Get supported providers for a channel
     */
    public function getSupportedProviders(string $channel): array
    {
        return NotificationChannelFactory::getProvidersForChannel($channel);
    }

    /**
     * Get all supported channels
     */
    public function getSupportedChannels(): array
    {
        return NotificationChannelFactory::getSupportedChannels();
    }
}
