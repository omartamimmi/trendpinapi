<?php

namespace Modules\Notification\app\Factories;

use Modules\Notification\app\Contracts\NotificationChannelInterface;
use Modules\Notification\app\Providers\Email\SmtpEmailProvider;
use Modules\Notification\app\Providers\Sms\TwilioSmsProvider;
use Modules\Notification\app\Providers\WhatsApp\TwilioWhatsAppProvider;
use Modules\Notification\app\Providers\WhatsApp\MetaWhatsAppProvider;
use Modules\Notification\app\Providers\Push\FirebasePushProvider;
use InvalidArgumentException;

/**
 * Factory for creating notification channel providers
 * Implements the Factory Pattern
 */
class NotificationChannelFactory
{
    /**
     * Registered channel providers
     */
    private static array $providers = [
        'email' => [
            'smtp' => SmtpEmailProvider::class,
        ],
        'smtp' => [
            'smtp' => SmtpEmailProvider::class,
        ],
        'sms' => [
            'twilio' => TwilioSmsProvider::class,
        ],
        'whatsapp' => [
            'twilio' => TwilioWhatsAppProvider::class,
            'meta' => MetaWhatsAppProvider::class,
        ],
        'push' => [
            'firebase' => FirebasePushProvider::class,
        ],
    ];

    /**
     * Create a channel provider instance
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $channel, string $provider, array $config = []): NotificationChannelInterface
    {
        $channel = strtolower($channel);
        $provider = strtolower($provider);

        if (!isset(self::$providers[$channel])) {
            throw new InvalidArgumentException("Unknown channel: {$channel}");
        }

        if (!isset(self::$providers[$channel][$provider])) {
            throw new InvalidArgumentException("Unknown provider '{$provider}' for channel '{$channel}'");
        }

        $providerClass = self::$providers[$channel][$provider];

        return new $providerClass($config);
    }

    /**
     * Create a channel provider from credentials DTO
     */
    public static function createFromCredentials(
        \Modules\Notification\app\DTOs\ChannelCredentials $credentials
    ): NotificationChannelInterface {
        return self::create(
            $credentials->channel,
            $credentials->provider,
            $credentials->credentials
        );
    }

    /**
     * Get available providers for a channel
     */
    public static function getProvidersForChannel(string $channel): array
    {
        $channel = strtolower($channel);

        if (!isset(self::$providers[$channel])) {
            return [];
        }

        return array_keys(self::$providers[$channel]);
    }

    /**
     * Get all supported channels
     */
    public static function getSupportedChannels(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Check if a channel/provider combination is supported
     */
    public static function isSupported(string $channel, string $provider): bool
    {
        $channel = strtolower($channel);
        $provider = strtolower($provider);

        return isset(self::$providers[$channel][$provider]);
    }

    /**
     * Register a custom provider
     */
    public static function registerProvider(string $channel, string $provider, string $class): void
    {
        if (!is_subclass_of($class, NotificationChannelInterface::class)) {
            throw new InvalidArgumentException(
                "Provider class must implement NotificationChannelInterface"
            );
        }

        $channel = strtolower($channel);
        $provider = strtolower($provider);

        if (!isset(self::$providers[$channel])) {
            self::$providers[$channel] = [];
        }

        self::$providers[$channel][$provider] = $class;
    }

    /**
     * Get the default provider for a channel
     */
    public static function getDefaultProvider(string $channel): ?string
    {
        $channel = strtolower($channel);

        if (!isset(self::$providers[$channel])) {
            return null;
        }

        $providers = array_keys(self::$providers[$channel]);
        return $providers[0] ?? null;
    }
}
