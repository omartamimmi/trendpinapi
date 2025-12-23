<?php

namespace Modules\BankOffer\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class BankOfferRedemption extends Model
{
    protected $fillable = [
        'bank_offer_id',
        'brand_id',
        'branch_id',
        'user_id',
        'amount',
        'discount_applied',
        'redeemed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    /**
     * The bank offer that was redeemed
     */
    public function bankOffer(): BelongsTo
    {
        return $this->belongsTo(BankOffer::class);
    }

    /**
     * The brand where redemption occurred
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * The branch where redemption occurred
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * The user who redeemed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: By user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By offer
     */
    public function scopeForOffer($query, int $offerId)
    {
        return $query->where('bank_offer_id', $offerId);
    }

    /**
     * Scope: By date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('redeemed_at', [$startDate, $endDate]);
    }

    /**
     * Create a redemption record
     */
    public static function recordRedemption(
        int $offerId,
        int $userId,
        ?int $brandId = null,
        ?int $branchId = null,
        ?float $amount = null,
        ?float $discountApplied = null
    ): self {
        $redemption = self::create([
            'bank_offer_id' => $offerId,
            'brand_id' => $brandId,
            'branch_id' => $branchId,
            'user_id' => $userId,
            'amount' => $amount,
            'discount_applied' => $discountApplied,
            'redeemed_at' => now(),
        ]);

        // Increment offer claims count
        BankOffer::find($offerId)?->incrementClaims();

        return $redemption;
    }
}
