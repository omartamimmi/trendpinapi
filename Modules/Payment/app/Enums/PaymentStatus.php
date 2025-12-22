<?php

namespace Modules\Payment\app\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::AUTHORIZED => 'Authorized',
            self::CAPTURED => 'Captured',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [self::COMPLETED, self::CAPTURED]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED, self::REFUNDED]);
    }
}
