<?php

namespace Modules\Notification\app\DTOs;

/**
 * Data Transfer Object for credential test result
 */
class CredentialTestResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $details = null,
        public readonly ?string $errorCode = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Create a successful result
     */
    public static function success(string $message, ?string $details = null, array $metadata = []): self
    {
        return new self(
            success: true,
            message: $message,
            details: $details,
            metadata: $metadata,
        );
    }

    /**
     * Create a failed result
     */
    public static function failure(string $message, ?string $details = null, ?string $errorCode = null): self
    {
        return new self(
            success: false,
            message: $message,
            details: $details,
            errorCode: $errorCode,
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
            'details' => $this->details,
            'error_code' => $this->errorCode,
            'metadata' => $this->metadata,
        ];
    }
}
