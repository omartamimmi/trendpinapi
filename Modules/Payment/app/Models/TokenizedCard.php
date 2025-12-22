<?php

namespace Modules\Payment\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\CardType;

class TokenizedCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'gateway',
        'gateway_token',
        'gateway_customer_id',
        'card_last_four',
        'card_brand',
        'card_expiry_month',
        'card_expiry_year',
        'cardholder_name',
        'nickname',
        'bank_id',
        'card_type_id',
        'bin_prefix',
        'is_active',
        'is_default',
        'is_verified',
        'wallet_type',
        'last_used_at',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
    ];

    protected $hidden = [
        'gateway_token',
        'gateway_customer_id',
    ];

    /**
     * Owner of the card
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detected bank
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Card type
     */
    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    /**
     * Transactions made with this card
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Scope: Active cards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: For user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get card display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->nickname) {
            return $this->nickname;
        }

        $brandName = ucfirst($this->card_brand ?? 'Card');
        return "{$brandName} •••• {$this->card_last_four}";
    }

    /**
     * Get card expiry display
     */
    public function getExpiryDisplayAttribute(): string
    {
        return "{$this->card_expiry_month}/{$this->card_expiry_year}";
    }

    /**
     * Check if card is expired
     */
    public function isExpired(): bool
    {
        $year = (int) $this->card_expiry_year;
        $month = (int) $this->card_expiry_month;

        // Handle 2-digit year
        if ($year < 100) {
            $year += 2000;
        }

        $expiryDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();
        return $expiryDate->isPast();
    }

    /**
     * Check if this is a wallet card
     */
    public function isWalletCard(): bool
    {
        return !empty($this->wallet_type);
    }

    /**
     * Record usage
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Set as default card
     */
    public function setAsDefault(): void
    {
        // Remove default from other cards
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get user's default card
     */
    public static function getDefaultForUser(int $userId): ?self
    {
        return self::forUser($userId)
            ->active()
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get all active cards for user
     */
    public static function getForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forUser($userId)
            ->active()
            ->with(['bank.logo', 'cardType'])
            ->orderByDesc('is_default')
            ->orderByDesc('last_used_at')
            ->get();
    }
}
