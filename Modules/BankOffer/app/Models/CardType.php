<?php

namespace Modules\BankOffer\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Media\Models\MediaFile;

class CardType extends Model
{
    protected $fillable = [
        'bank_id',
        'name',
        'name_ar',
        'logo_id',
        'card_network',
        'bin_prefixes',
        'card_color',
        'status',
    ];

    protected $casts = [
        'card_network' => 'string',
        'status' => 'string',
        'bin_prefixes' => 'array',
    ];

    /**
     * Bank that owns this card type (nullable for generic cards)
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Offers for this card type
     */
    public function offers(): HasMany
    {
        return $this->hasMany(BankOffer::class);
    }

    /**
     * Logo relationship
     */
    public function logo()
    {
        return $this->belongsTo(MediaFile::class, 'logo_id');
    }

    /**
     * Scope: Active card types
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Generic cards (no bank)
     */
    public function scopeGeneric($query)
    {
        return $query->whereNull('bank_id');
    }

    /**
     * Scope: By card network
     */
    public function scopeNetwork($query, string $network)
    {
        return $query->where('card_network', $network);
    }

    /**
     * Check if this is a generic card type
     */
    public function isGeneric(): bool
    {
        return is_null($this->bank_id);
    }

    /**
     * Find card type by BIN (first 6 digits of card number)
     *
     * @param string $bin First 6 digits of card number
     * @return CardType|null
     */
    public static function findByBin(string $bin): ?self
    {
        // Clean input - only keep digits
        $bin = preg_replace('/\D/', '', $bin);

        // Need at least 6 digits
        if (strlen($bin) < 6) {
            return null;
        }

        // Get first 6 digits
        $bin6 = substr($bin, 0, 6);

        // Search for matching card type
        $cardTypes = self::with(['bank', 'logo'])
            ->where('status', 'active')
            ->whereNotNull('bin_prefixes')
            ->get();

        foreach ($cardTypes as $cardType) {
            $prefixes = $cardType->bin_prefixes ?? [];

            foreach ($prefixes as $prefix) {
                // Check if the BIN starts with any of the prefixes
                if (str_starts_with($bin6, (string) $prefix)) {
                    return $cardType;
                }
            }
        }

        return null;
    }

    /**
     * Detect card network from BIN
     *
     * @param string $bin First 6 digits
     * @return string|null
     */
    public static function detectNetworkFromBin(string $bin): ?string
    {
        $bin = preg_replace('/\D/', '', $bin);

        if (strlen($bin) < 1) {
            return null;
        }

        // Visa: starts with 4
        if (str_starts_with($bin, '4')) {
            return 'visa';
        }

        // Mastercard: starts with 51-55 or 2221-2720
        $first2 = substr($bin, 0, 2);
        $first4 = substr($bin, 0, 4);
        if (in_array($first2, ['51', '52', '53', '54', '55'])) {
            return 'mastercard';
        }
        if (strlen($first4) >= 4) {
            $first4Int = (int) $first4;
            if ($first4Int >= 2221 && $first4Int <= 2720) {
                return 'mastercard';
            }
        }

        // Amex: starts with 34 or 37
        if (in_array($first2, ['34', '37'])) {
            return 'amex';
        }

        return 'other';
    }

    /**
     * Check if this card type matches a given BIN
     */
    public function matchesBin(string $bin): bool
    {
        $bin = preg_replace('/\D/', '', $bin);
        $bin6 = substr($bin, 0, 6);

        $prefixes = $this->bin_prefixes ?? [];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($bin6, (string) $prefix)) {
                return true;
            }
        }

        return false;
    }
}
