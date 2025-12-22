<?php

namespace Modules\Payment\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BankOffer\app\Models\Bank;

class CliqPaymentRequest extends Model
{
    protected $fillable = [
        'qr_session_id',
        'request_id',
        'jopacc_reference',
        'amount',
        'currency',
        'sender_bank_id',
        'sender_alias',
        'sender_name',
        'receiver_alias',
        'receiver_name',
        'status',
        'failure_reason',
        'failure_details',
        'deep_link',
        'universal_link',
        'expires_at',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * QR Session
     */
    public function qrSession(): BelongsTo
    {
        return $this->belongsTo(QrPaymentSession::class, 'qr_session_id');
    }

    /**
     * Sender's bank
     */
    public function senderBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'sender_bank_id');
    }

    /**
     * Generate unique request ID
     */
    public static function generateRequestId(): string
    {
        return 'CLIQ-' . date('YmdHis') . '-' . rand(1000, 9999);
    }

    /**
     * Check if expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark as sent to bank
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent_to_bank',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(?string $jopaccReference = null): void
    {
        $this->update([
            'status' => 'completed',
            'jopacc_reference' => $jopaccReference,
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
     * Scope: Pending requests
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
     * Get bank deep link schemes
     */
    public static function getBankDeepLinkSchemes(): array
    {
        return [
            'arab_bank' => ['scheme' => 'arabbank', 'host' => 'pay'],
            'jordan_islamic' => ['scheme' => 'jib', 'host' => 'pay'],
            'housing_bank' => ['scheme' => 'hbtf', 'host' => 'transfer'],
            'cairo_amman' => ['scheme' => 'cab', 'host' => 'cliq'],
            'bank_of_jordan' => ['scheme' => 'boj', 'host' => 'pay'],
            'capital_bank' => ['scheme' => 'capitalbank', 'host' => 'cliq'],
            'societe_generale' => ['scheme' => 'sgbj', 'host' => 'pay'],
            'invest_bank' => ['scheme' => 'investbank', 'host' => 'cliq'],
        ];
    }

    /**
     * Generate deep link for bank
     */
    public function generateDeepLinks(): array
    {
        $bankSlug = $this->senderBank?->slug ?? '';
        $schemes = self::getBankDeepLinkSchemes();
        $config = $schemes[$bankSlug] ?? null;

        $params = http_build_query([
            'amount' => $this->amount,
            'ref' => $this->request_id,
            'merchant' => $this->receiver_name,
            'alias' => $this->receiver_alias,
        ]);

        $deepLink = $config
            ? "{$config['scheme']}://{$config['host']}?{$params}"
            : null;

        $universalLink = config('app.url') . "/cliq/{$this->request_id}";

        $this->update([
            'deep_link' => $deepLink,
            'universal_link' => $universalLink,
        ]);

        return [
            'deep_link' => $deepLink,
            'universal_link' => $universalLink,
        ];
    }
}
