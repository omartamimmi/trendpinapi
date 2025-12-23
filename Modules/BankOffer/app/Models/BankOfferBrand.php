<?php

namespace Modules\BankOffer\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class BankOfferBrand extends Model
{
    protected $fillable = [
        'bank_offer_id',
        'brand_id',
        'all_branches',
        'branch_ids',
        'status',
        'requested_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'all_branches' => 'boolean',
        'branch_ids' => 'array',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * The bank offer
     */
    public function bankOffer(): BelongsTo
    {
        return $this->belongsTo(BankOffer::class);
    }

    /**
     * The brand (shop)
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * User who approved this request
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if offer is valid for a specific branch
     */
    public function isValidForBranch(int $branchId): bool
    {
        if ($this->all_branches) {
            return true;
        }

        return in_array($branchId, $this->branch_ids ?? []);
    }

    /**
     * Get branches where this offer is valid
     */
    public function getValidBranches()
    {
        if ($this->all_branches) {
            return $this->brand->branches;
        }

        return Branch::whereIn('id', $this->branch_ids ?? [])->get();
    }

    /**
     * Approve this request
     */
    public function approve(int $approverId): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
    }

    /**
     * Reject this request
     */
    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
        ]);
    }
}
