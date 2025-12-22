<?php

namespace Modules\Payment\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'qr_session_id',
        'customer_id',
        'brand_id',
        'branch_id',
        'original_amount',
        'discount_amount',
        'final_amount',
        'currency',
        'gateway_fee',
        'platform_fee',
        'net_amount',
        'bank_offer_id',
        'bank_id',
        'discount_type',
        'discount_value',
        'payment_method',
        'gateway',
        'tokenized_card_id',
        'card_last_four',
        'card_brand',
        'card_bin',
        'gateway_transaction_id',
        'gateway_charge_id',
        'gateway_authorization_code',
        'gateway_response',
        'requires_3ds',
        'auth_url',
        'auth_status',
        'status',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'failure_code',
        'failure_message',
        'failure_details',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_ip',
        'customer_device',
        'metadata',
        'notes',
        'authorized_at',
        'captured_at',
        'completed_at',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'requires_3ds' => 'boolean',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected $hidden = [
        'gateway_response',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference)) {
                $model->reference = self::generateReference();
            }
        });
    }

    /**
     * Generate unique reference
     */
    public static function generateReference(): string
    {
        return 'PAY-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)) . '-' . time();
    }

    /**
     * QR Session
     */
    public function qrSession(): BelongsTo
    {
        return $this->belongsTo(QrPaymentSession::class, 'qr_session_id');
    }

    /**
     * Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * User (alias for customer for admin views)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Brand (retailer)
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Bank offer
     */
    public function bankOffer(): BelongsTo
    {
        return $this->belongsTo(BankOffer::class);
    }

    /**
     * Bank
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Tokenized card used
     */
    public function tokenizedCard(): BelongsTo
    {
        return $this->belongsTo(TokenizedCard::class);
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: For customer
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: For brand
     */
    public function scopeForBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Scope: For branch
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope: By payment method
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: By gateway
     */
    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['completed', 'captured']);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->isSuccessful() &&
            $this->refunded_amount < $this->final_amount;
    }

    /**
     * Get remaining refundable amount
     */
    public function getRefundableAmountAttribute(): float
    {
        return max(0, $this->final_amount - $this->refunded_amount);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $gatewayTransactionId, ?string $chargeId = null): void
    {
        $this->update([
            'status' => 'completed',
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_charge_id' => $chargeId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $code, string $message, ?string $details = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_code' => $code,
            'failure_message' => $message,
            'failure_details' => $details,
        ]);
    }

    /**
     * Process refund
     */
    public function processRefund(float $amount, string $reason): void
    {
        $this->update([
            'refunded_amount' => $this->refunded_amount + $amount,
            'refund_reason' => $reason,
            'refunded_at' => now(),
            'status' => ($this->refunded_amount + $amount >= $this->final_amount)
                ? 'refunded'
                : 'partially_refunded',
        ]);
    }

    /**
     * Calculate fees
     */
    public function calculateFees(): void
    {
        $feePercentage = config("payment.fees.{$this->payment_method}", 2.5);
        $this->gateway_fee = round($this->final_amount * ($feePercentage / 100), 2);
        $this->net_amount = $this->final_amount - $this->gateway_fee - $this->platform_fee;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'authorized' => 'Authorized',
            'captured' => 'Captured',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'card' => 'Credit/Debit Card',
            'apple_pay' => 'Apple Pay',
            'google_pay' => 'Google Pay',
            'cliq' => 'CliQ',
            default => ucfirst($this->payment_method),
        };
    }
}
