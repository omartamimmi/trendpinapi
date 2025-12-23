<?php

namespace Modules\BankOffer\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBank extends Model
{
    protected $fillable = [
        'user_id',
        'bank_id',
        'card_type_id',
        'card_last_four',
        'card_nickname',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * The user who owns this bank card
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The bank
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * The card type (optional)
     */
    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    /**
     * Get display name for this card
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->card_nickname) {
            return $this->card_nickname;
        }

        $name = $this->bank?->name ?? 'Card';

        if ($this->cardType) {
            $name .= ' ' . $this->cardType->name;
        }

        if ($this->card_last_four) {
            $name .= ' •••• ' . $this->card_last_four;
        }

        return $name;
    }

    /**
     * Set this card as primary and unset others
     */
    public function makePrimary(): void
    {
        // Unset other primary cards for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    /**
     * Scope: Primary cards only
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: For a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
