<?php

namespace Modules\Notification\app\DTOs;

/**
 * Data Transfer Object for notification send result
 */
class NotificationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $messageId = null,
        public readonly ?string $providerId = null,
        public readonly array $metadata = [],
        public readonly ?string $errorCode = null,
        public readonly ?\Throwable $exception = null,
    ) {}

    /**
     * Create a successful result
     */
    public static function success(string $message, ?string $messageId = null, array $metadata = []): self
    {
        return new self(
            success: true,
            message: $message,
            messageId: $messageId,
            metadata: $metadata,
        );
    }

    /**
     * Create a failed result
     */
    public static function failure(string $message, ?string $errorCode = null, ?\Throwable $exception = null): self
    {
        return new self(
            success: false,
            message: $message,
            errorCode: $errorCode,
            exception: $exception,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'message_id' => $this->messageId,
            'provider_id' => $this->providerId,
            'metadata' => $this->metadata,
            'error_code' => $this->errorCode,
        ];
    }
}
