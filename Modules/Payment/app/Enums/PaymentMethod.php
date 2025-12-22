<?php

namespace Modules\Payment\app\Enums;

enum PaymentMethod: string
{
    case CARD = 'card';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';
    case CLIQ = 'cliq';

    public function label(): string
    {
        return match ($this) {
            self::CARD => 'Credit/Debit Card',
            self::APPLE_PAY => 'Apple Pay',
            self::GOOGLE_PAY => 'Google Pay',
            self::CLIQ => 'CliQ',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::CARD => 'بطاقة ائتمان/خصم',
            self::APPLE_PAY => 'Apple Pay',
            self::GOOGLE_PAY => 'Google Pay',
            self::CLIQ => 'كليك',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CARD => 'credit-card',
            self::APPLE_PAY => 'apple',
            self::GOOGLE_PAY => 'google',
            self::CLIQ => 'bank',
        };
    }

    public function isWallet(): bool
    {
        return in_array($this, [self::APPLE_PAY, self::GOOGLE_PAY]);
    }

    public function requiresRedirect(): bool
    {
        return $this === self::CARD;
    }
}
