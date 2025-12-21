<?php

namespace Modules\BankOffer\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Business\app\Models\Brand;

class BankOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bank_id',
        'card_type_id',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'offer_type',
        'offer_value',
        'min_purchase_amount',
        'max_discount_amount',
        'start_date',
        'end_date',
        'terms',
        'terms_ar',
        'redemption_type',
        'status',
        'total_claims',
        'max_claims',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'offer_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'total_claims' => 'integer',
        'max_claims' => 'integer',
    ];

    /**
     * Bank that created this offer
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Card type (optional - null means all cards from bank)
     */
    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    /**
     * Brands participating in this offer
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'bank_offer_brands')
            ->withPivot(['all_branches', 'branch_ids', 'status', 'requested_at', 'approved_at', 'approved_by'])
            ->withTimestamps();
    }

    /**
     * Approved brands only
     */
    public function approvedBrands(): BelongsToMany
    {
        return $this->brands()->wherePivot('status', 'approved');
    }

    /**
     * Pending brand requests
     */
    public function pendingBrands(): BelongsToMany
    {
        return $this->brands()->wherePivot('status', 'pending');
    }

    /**
     * Brand pivot records (BankOfferBrand models)
     */
    public function brandPivots(): HasMany
    {
        return $this->hasMany(BankOfferBrand::class);
    }

    /**
     * Participating brands (alias for brandPivots)
     */
    public function participatingBrands(): HasMany
    {
        return $this->hasMany(BankOfferBrand::class);
    }

    /**
     * Redemptions for this offer
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(BankOfferRedemption::class);
    }

    /**
     * User who created the offer
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who approved the offer
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Active offers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope: Pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: By bank
     */
    public function scopeForBank($query, int $bankId)
    {
        return $query->where('bank_id', $bankId);
    }

    /**
     * Check if offer is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->start_date <= now()
            && $this->end_date >= now()
            && ($this->max_claims === null || $this->total_claims < $this->max_claims);
    }

    /**
     * Check if offer has reached claim limit
     */
    public function hasReachedLimit(): bool
    {
        return $this->max_claims !== null && $this->total_claims >= $this->max_claims;
    }

    /**
     * Increment total claims
     */
    public function incrementClaims(): void
    {
        $this->increment('total_claims');
    }

    /**
     * Get formatted discount display
     */
    public function getDiscountDisplayAttribute(): string
    {
        return match ($this->offer_type) {
            'percentage' => $this->offer_value . '% Off',
            'fixed' => 'JOD ' . number_format($this->offer_value, 2) . ' Off',
            'cashback' => $this->offer_value . '% Cashback',
            default => $this->offer_value,
        };
    }

    /**
     * Calculate discount for a given amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->min_purchase_amount && $amount < $this->min_purchase_amount) {
            return 0;
        }

        $discount = match ($this->offer_type) {
            'percentage', 'cashback' => $amount * ($this->offer_value / 100),
            'fixed' => $this->offer_value,
            default => 0,
        };

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return round($discount, 2);
    }
}
