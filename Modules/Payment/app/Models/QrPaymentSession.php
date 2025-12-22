<?php

namespace Modules\Payment\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class QrPaymentSession extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'session_code',
        'qr_code_data',
        'qr_code_image',
        'brand_id',
        'branch_id',
        'created_by_user_id',
        'amount',
        'currency',
        'description',
        'reference',
        'customer_id',
        'scanned_at',
        'original_amount',
        'discount_amount',
        'final_amount',
        'bank_offer_id',
        'transaction_id',
        'gateway',
        'gateway_transaction_id',
        'payment_method',
        'status',
        'failure_reason',
        'failure_details',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'scanned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Brand (retailer)
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Retailer (alias for brand for admin views)
     */
    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Staff who created
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Customer who scanned
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Bank offer applied
     */
    public function bankOffer(): BelongsTo
    {
        return $this->belongsTo(BankOffer::class);
    }

    /**
     * Payment transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    /**
     * Payment (alias for transaction for admin views)
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    /**
     * CliQ request (if applicable)
     */
    public function cliqRequest(): HasOne
    {
        return $this->hasOne(CliqPaymentRequest::class, 'qr_session_id');
    }

    /**
     * Generate unique session code
     */
    public static function generateSessionCode(): string
    {
        do {
            $code = 'TRP-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 9));
        } while (self::where('session_code', $code)->exists());

        return $code;
    }

    /**
     * Generate QR code
     */
    public function generateQrCode(): void
    {
        $url = config('app.url') . '/pay/' . $this->session_code;
        $this->qr_code_data = $url;

        // Using simple-qrcode package if available
        if (class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $qrImage = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(config('payment.qr_size', 300))
                ->margin(2)
                ->generate($url);

            $this->qr_code_image = 'data:image/png;base64,' . base64_encode($qrImage);
        } else {
            // Fallback: Use Google Charts API for QR generation
            $size = config('payment.qr_size', 300);
            $this->qr_code_image = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . urlencode($url);
        }

        $this->save();
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if can be scanned
     */
    public function canBeScanned(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Check if can be paid
     */
    public function canBePaid(): bool
    {
        return in_array($this->status, ['scanned']) && !$this->isExpired();
    }

    /**
     * Mark as scanned
     */
    public function markAsScanned(int $customerId): void
    {
        $this->update([
            'status' => 'scanned',
            'customer_id' => $customerId,
            'scanned_at' => now(),
        ]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(float $discountAmount = 0, ?int $bankOfferId = null, ?string $gateway = null, ?string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'processing',
            'original_amount' => $this->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $this->amount - $discountAmount,
            'bank_offer_id' => $bankOfferId,
            'gateway' => $gateway,
            'payment_method' => $paymentMethod,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(int $transactionId, string $gatewayTransactionId): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_transaction_id' => $gatewayTransactionId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $reason, ?string $details = null): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'failure_details' => $details,
        ]);
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Scope: Pending sessions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Active (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
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
     * Scope: Completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get remaining time in seconds
     */
    public function getRemainingSecondsAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInSeconds($this->expires_at);
    }
}
