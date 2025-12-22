<?php

namespace Modules\Payment\Services;

use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferRedemption;
use Modules\BankOffer\app\Models\CardType;
use Modules\Business\app\Models\Branch;

class BankDiscountService
{
    /**
     * Calculate applicable discount for a payment based on card BIN
     */
    public function calculateDiscount(
        float $amount,
        ?string $cardBin,
        int $branchId,
        ?int $userId = null
    ): array {
        // Validate BIN
        if (!$cardBin || strlen($cardBin) < 6) {
            return $this->noDiscount($amount, 'Card BIN not provided');
        }

        // Use existing CardType::findByBin() to detect bank
        $cardType = CardType::findByBin($cardBin);

        if (!$cardType || !$cardType->bank_id) {
            return $this->noDiscount($amount, 'Bank not recognized from card');
        }

        return $this->calculateDiscountForBank(
            $amount,
            $cardType->bank_id,
            $branchId,
            $userId,
            $cardType
        );
    }

    /**
     * Calculate discount when bank is already known (for CliQ)
     */
    public function calculateDiscountByBank(
        float $amount,
        int $bankId,
        int $branchId,
        ?int $userId = null
    ): array {
        return $this->calculateDiscountForBank($amount, $bankId, $branchId, $userId);
    }

    /**
     * Internal method to calculate discount for a specific bank
     */
    private function calculateDiscountForBank(
        float $amount,
        int $bankId,
        int $branchId,
        ?int $userId = null,
        ?CardType $cardType = null
    ): array {
        // Get branch
        $branch = Branch::find($branchId);
        if (!$branch) {
            return $this->noDiscount($amount, 'Branch not found');
        }

        // Find applicable active bank offers for this branch + bank
        $offers = BankOffer::query()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('bank_id', $bankId)
            // Offer applies to this card type OR all cards from bank
            ->where(function ($q) use ($cardType) {
                $q->whereNull('card_type_id');
                if ($cardType) {
                    $q->orWhere('card_type_id', $cardType->id);
                }
            })
            // Offer is linked to this branch's brand
            ->whereHas('brandPivots', function ($q) use ($branch, $branchId) {
                $q->where('brand_id', $branch->brand_id)
                    ->where('status', 'approved')
                    // Applies to all branches OR this specific branch
                    ->where(function ($subQ) use ($branchId) {
                        $subQ->where('all_branches', true)
                            ->orWhereJsonContains('branch_ids', $branchId);
                    });
            })
            // Has not reached max claims
            ->where(function ($q) {
                $q->whereNull('max_claims')
                    ->orWhereRaw('total_claims < max_claims');
            })
            // Meets minimum purchase amount
            ->where(function ($q) use ($amount) {
                $q->whereNull('min_purchase_amount')
                    ->orWhere('min_purchase_amount', '<=', $amount);
            })
            ->orderByDesc('offer_value')
            ->with(['bank.logo'])
            ->get();

        if ($offers->isEmpty()) {
            return $this->noDiscount($amount, 'No active offers for this bank at this branch');
        }

        // Get the best offer (highest discount value)
        $bestOffer = $offers->first();

        // Use existing calculateDiscount method from BankOffer model
        $discountAmount = $bestOffer->calculateDiscount($amount);

        // Apply max discount cap if set
        if ($bestOffer->max_discount_amount && $discountAmount > $bestOffer->max_discount_amount) {
            $discountAmount = $bestOffer->max_discount_amount;
        }

        $bank = $bestOffer->bank;

        return [
            'has_discount' => $discountAmount > 0,
            'original_amount' => round($amount, 2),
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => round($amount - $discountAmount, 2),
            'bank_offer_id' => $bestOffer->id,
            'bank' => $bank ? [
                'id' => $bank->id,
                'name' => $bank->name,
                'name_ar' => $bank->name_ar,
                'logo' => $bank->logo?->url,
            ] : null,
            'card_type' => $cardType ? [
                'id' => $cardType->id,
                'name' => $cardType->name,
                'card_network' => $cardType->card_network,
            ] : null,
            'offer' => [
                'id' => $bestOffer->id,
                'title' => $bestOffer->title,
                'title_ar' => $bestOffer->title_ar,
                'type' => $bestOffer->offer_type,
                'value' => $bestOffer->offer_value,
                'display' => $bestOffer->discount_display,
                'terms' => $bestOffer->terms,
                'end_date' => $bestOffer->end_date->toDateString(),
            ],
            'message' => "Save JOD " . number_format($discountAmount, 2) . " with {$bank?->name}!",
            'message_ar' => "وفّر " . number_format($discountAmount, 2) . " دينار مع {$bank?->name_ar}!",
        ];
    }

    /**
     * Get all available offers for a branch (for display)
     */
    public function getAvailableOffers(int $branchId, float $amount): array
    {
        $branch = Branch::find($branchId);
        if (!$branch) {
            return [];
        }

        $offers = BankOffer::query()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereHas('brandPivots', function ($q) use ($branch, $branchId) {
                $q->where('brand_id', $branch->brand_id)
                    ->where('status', 'approved')
                    ->where(function ($subQ) use ($branchId) {
                        $subQ->where('all_branches', true)
                            ->orWhereJsonContains('branch_ids', $branchId);
                    });
            })
            ->with('bank.logo')
            ->get();

        return $offers->map(function ($offer) use ($amount) {
            $savings = $offer->calculateDiscount($amount);
            return [
                'bank_id' => $offer->bank_id,
                'bank_name' => $offer->bank->name,
                'bank_name_ar' => $offer->bank->name_ar,
                'bank_logo' => $offer->bank->logo?->url,
                'offer_display' => $offer->discount_display,
                'potential_savings' => round($savings, 2),
            ];
        })->unique('bank_id')->values()->toArray();
    }

    /**
     * Record redemption after successful payment
     */
    public function recordRedemption(
        int $bankOfferId,
        int $userId,
        int $branchId,
        float $originalAmount,
        float $discountApplied
    ): BankOfferRedemption {
        $branch = Branch::find($branchId);

        // Increment total claims on the offer
        $offer = BankOffer::find($bankOfferId);
        $offer?->incrementClaims();

        return BankOfferRedemption::create([
            'bank_offer_id' => $bankOfferId,
            'user_id' => $userId,
            'brand_id' => $branch?->brand_id,
            'branch_id' => $branchId,
            'amount' => $originalAmount,
            'discount_applied' => $discountApplied,
            'redeemed_at' => now(),
        ]);
    }

    /**
     * Return no discount result
     */
    private function noDiscount(float $amount, string $reason = ''): array
    {
        return [
            'has_discount' => false,
            'original_amount' => round($amount, 2),
            'discount_amount' => 0,
            'final_amount' => round($amount, 2),
            'bank_offer_id' => null,
            'bank' => null,
            'card_type' => null,
            'offer' => null,
            'message' => null,
            'message_ar' => null,
            'reason' => $reason,
        ];
    }
}
