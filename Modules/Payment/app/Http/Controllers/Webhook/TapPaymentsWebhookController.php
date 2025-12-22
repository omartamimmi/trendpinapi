<?php

namespace Modules\Payment\app\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Payment\app\Models\PaymentTransaction;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\Services\BankDiscountService;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\app\Events\QrSessionCompleted;
use Modules\Payment\app\Events\PaymentCompleted;
use Modules\Payment\app\Events\PaymentFailed;

class TapPaymentsWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly BankDiscountService $discountService,
    ) {}

    /**
     * Handle Tap Payments webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel('payment')->info('Tap Payments Webhook Received', [
            'payload' => $this->sanitizePayload($payload),
        ]);

        // Verify webhook signature
        $signature = $request->header('X-Tap-Signature') ?? $request->header('Hashstring');
        if (!$this->verifySignature($request->getContent(), $signature)) {
            Log::channel('payment')->warning('Tap Payments Webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            // Determine event type
            $eventType = $payload['object'] ?? 'charge';
            $status = strtoupper($payload['status'] ?? 'UNKNOWN');

            // Find payment by reference
            $reference = $payload['reference']['transaction'] ?? null;
            $chargeId = $payload['id'] ?? null;

            if (!$reference && !$chargeId) {
                return response()->json(['error' => 'No reference found'], 400);
            }

            // Find the QR session or payment transaction
            $session = null;
            $transaction = null;

            if ($reference) {
                $session = QrPaymentSession::where('session_code', $reference)
                    ->orWhereHas('payment', fn($q) => $q->where('reference', $reference))
                    ->first();

                $transaction = PaymentTransaction::where('reference', $reference)->first();
            }

            if (!$session && $chargeId) {
                $session = QrPaymentSession::where('gateway_transaction_id', $chargeId)->first();
                $transaction = PaymentTransaction::where('gateway_transaction_id', $chargeId)->first();
            }

            // Process based on status
            $mappedStatus = $this->mapStatus($status);

            if ($mappedStatus === 'completed') {
                return $this->handleSuccess($session, $transaction, $payload);
            } elseif (in_array($mappedStatus, ['failed', 'cancelled'])) {
                return $this->handleFailure($session, $transaction, $payload, $mappedStatus);
            } elseif ($mappedStatus === 'refunded') {
                return $this->handleRefund($session, $transaction, $payload);
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::channel('payment')->error('Tap Payments Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    private function handleSuccess(?QrPaymentSession $session, ?PaymentTransaction $transaction, array $payload): JsonResponse
    {
        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'gateway_transaction_id' => $payload['id'],
                'gateway_response' => $payload,
                'card_last_four' => $payload['card']['last_four'] ?? $transaction->card_last_four,
                'card_brand' => strtolower($payload['card']['brand'] ?? $transaction->card_brand),
                'completed_at' => now(),
            ]);

            // Record bank offer redemption
            if ($transaction->bank_offer_id && $transaction->customer_id) {
                $this->discountService->recordRedemption(
                    bankOfferId: $transaction->bank_offer_id,
                    userId: $transaction->customer_id,
                    branchId: $transaction->branch_id,
                    originalAmount: $transaction->original_amount,
                    discountApplied: $transaction->discount_amount,
                );
            }

            // Broadcast event
            broadcast(new PaymentCompleted($transaction))->toOthers();
        }

        if ($session && $session->status !== 'completed') {
            $session->markAsCompleted(
                paymentId: $transaction?->id ?? $session->payment_id,
                transactionId: $payload['id'],
            );

            // Record bank offer redemption if not already done
            if ($session->bank_offer_id && !$transaction) {
                $this->discountService->recordRedemption(
                    bankOfferId: $session->bank_offer_id,
                    userId: $session->customer_id,
                    branchId: $session->branch_id,
                    originalAmount: $session->original_amount,
                    discountApplied: $session->discount_amount,
                );
            }

            // Broadcast event
            broadcast(new QrSessionCompleted($session))->toOthers();
        }

        Log::channel('payment')->info('Tap Payment Success', [
            'charge_id' => $payload['id'],
            'session_code' => $session?->session_code,
            'transaction_id' => $transaction?->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment recorded']);
    }

    /**
     * Handle failed payment
     */
    private function handleFailure(?QrPaymentSession $session, ?PaymentTransaction $transaction, array $payload, string $status): JsonResponse
    {
        $errorMessage = $payload['response']['message'] ?? 'Payment failed';

        if ($transaction) {
            $transaction->update([
                'status' => $status,
                'gateway_transaction_id' => $payload['id'],
                'gateway_response' => $payload,
                'error_message' => $errorMessage,
                'error_code' => $payload['response']['code'] ?? null,
            ]);

            // Broadcast failure event
            broadcast(new PaymentFailed($transaction, $errorMessage))->toOthers();
        }

        if ($session && in_array($session->status, ['processing', 'scanned'])) {
            // Revert session to scanned so customer can retry
            $session->update([
                'status' => 'scanned',
                'gateway_transaction_id' => $payload['id'],
            ]);
        }

        Log::channel('payment')->warning('Tap Payment Failed', [
            'charge_id' => $payload['id'],
            'error' => $errorMessage,
            'session_code' => $session?->session_code,
        ]);

        return response()->json(['success' => true, 'message' => 'Failure recorded']);
    }

    /**
     * Handle refund
     */
    private function handleRefund(?QrPaymentSession $session, ?PaymentTransaction $transaction, array $payload): JsonResponse
    {
        if ($transaction) {
            $refundAmount = $payload['amount'] ?? $transaction->amount;

            $transaction->update([
                'status' => 'refunded',
                'refund_amount' => $refundAmount,
                'refund_reason' => $payload['reason'] ?? 'Refund processed',
                'refunded_at' => now(),
                'gateway_response' => $payload,
            ]);
        }

        Log::channel('payment')->info('Tap Payment Refunded', [
            'charge_id' => $payload['id'],
            'transaction_id' => $transaction?->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Refund recorded']);
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = config('payment.gateways.tap.webhook_secret');

        if (empty($secret)) {
            // Skip verification if no secret configured (not recommended for production)
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }

    /**
     * Map Tap status to standard status
     */
    private function mapStatus(string $status): string
    {
        return match ($status) {
            'INITIATED' => 'pending',
            'IN_PROGRESS' => 'processing',
            'CAPTURED' => 'completed',
            'AUTHORIZED' => 'authorized',
            'FAILED', 'DECLINED', 'RESTRICTED' => 'failed',
            'CANCELLED', 'ABANDONED', 'TIMEDOUT' => 'cancelled',
            'REFUNDED' => 'refunded',
            'VOID', 'VOIDED' => 'voided',
            default => 'unknown',
        };
    }

    /**
     * Sanitize payload for logging
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitive = ['card', 'source', 'customer'];

        return collect($payload)->map(function ($value, $key) use ($sensitive) {
            if (in_array($key, $sensitive) && is_array($value)) {
                return '[REDACTED]';
            }
            return $value;
        })->toArray();
    }
}
