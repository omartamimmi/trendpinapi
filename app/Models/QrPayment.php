<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QrPayment extends Model
{
    protected $fillable = [
        'merchant_id',
        'customer_id',
        'qr_code_reference',
        'amount',
        'currency',
        'description',
        'status',
        'qr_code_data',
        'qr_code_image',
        'expires_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Merchant (retailer) who created the QR code
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    /**
     * Customer who paid
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for expired payments
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                          ->where('expires_at', '<', now());
                    });
    }

    /**
     * Check if QR code is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now() && $this->status === 'pending';
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment can be paid
     */
    public function canBePaid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(User $customer): void
    {
        $this->update([
            'status' => 'completed',
            'customer_id' => $customer->id,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        if ($this->status === 'pending') {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled(): void
    {
        if ($this->status === 'pending') {
            $this->update(['status' => 'cancelled']);
        }
    }

    /**
     * Generate unique reference
     */
    public static function generateReference(): string
    {
        return 'QR-' . strtoupper(Str::random(10));
    }
}
