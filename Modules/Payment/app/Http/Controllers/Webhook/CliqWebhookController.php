<?php

namespace Modules\Payment\app\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Payment\app\Models\CliqPaymentRequest;
use Modules\Payment\app\Models\QrPaymentSession;
use Modules\Payment\app\Models\PaymentTransaction;
use Modules\Payment\Services\BankDiscountService;
use Modules\Payment\app\Events\QrSessionCompleted;
use Modules\Payment\app\Events\PaymentCompleted;

class CliqWebhookController extends Controller
{
    public function __construct(
        private readonly BankDiscountService $discountService,
    ) {}

    /**
     * Handle CliQ/JOPACC webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel('payment')->info('CliQ Webhook Received', [
            'payload' => $payload,
        ]);

        // Verify webhook signature from JOPACC
        if (!$this->verifyJopaccSignature($request)) {
            Log::channel('payment')->warning('CliQ Webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $transactionId = $payload['transaction_id'] ?? $payload['TransactionId'] ?? null;
            $status = strtoupper($payload['status'] ?? $payload['Status'] ?? 'UNKNOWN');

            if (!$transactionId) {
                return response()->json(['error' => 'Missing transaction ID'], 400);
            }

            // Find the CliQ payment request
            $cliqRequest = CliqPaymentRequest::where('request_id', $transactionId)->first();

            if (!$cliqRequest) {
                Log::channel('payment')->warning('CliQ Webhook: Request not found', [
                    'transaction_id' => $transactionId,
                ]);
                return response()->json(['error' => 'Request not found'], 404);
            }

            // Process based on status
            if (in_array($status, ['COMPLETED', 'ACSC', 'ACSP'])) {
                return $this->handleSuccess($cliqRequest, $payload);
            } elseif (in_array($status, ['FAILED', 'REJECTED', 'RJCT', 'CANC'])) {
                return $this->handleFailure($cliqRequest, $payload);
            } elseif (in_array($status, ['EXPIRED', 'PDNG'])) {
                return $this->handleExpired($cliqRequest, $payload);
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed']);
        } catch (\Exception $e) {
            Log::channel('payment')->error('CliQ Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle successful CliQ payment
     */
    private function handleSuccess(CliqPaymentRequest $cliqRequest, array $payload): JsonResponse
    {
        // Update CliQ request
        $cliqRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'jopacc_reference' => $payload['jopacc_reference'] ?? $payload['EndToEndId'] ?? null,
            'sender_name' => $payload['sender_name'] ?? $payload['DebtorName'] ?? null,
            'sender_account' => $payload['sender_account'] ?? null,
            'response_data' => $payload,
        ]);

        // Update QR session
        $session = $cliqRequest->qrSession;
        if ($session) {
            // Create payment transaction
            $transaction = PaymentTransaction::create([
                'reference' => $session->session_code . '-CLIQ',
                'gateway' => 'cliq',
                'gateway_transaction_id' => $cliqRequest->request_id,
                'payment_method' => 'cliq',
                'amount' => $session->final_amount,
                'original_amount' => $session->original_amount,
                'discount_amount' => $session->discount_amount,
                'currency' => 'JOD',
                'status' => 'completed',
                'customer_id' => $session->customer_id,
                'customer_name' => $session->customer?->name,
                'customer_email' => $session->customer?->email,
                'customer_phone' => $session->customer?->phone,
                'retailer_id' => $session->retailer_id,
                'brand_id' => $session->brand_id,
                'branch_id' => $session->branch_id,
                'retailer_name' => $session->brand?->name,
                'branch_name' => $session->branch?->name,
                'bank_offer_id' => $session->bank_offer_id,
                'qr_session_id' => $session->id,
                'gateway_response' => $payload,
                'completed_at' => now(),
            ]);

            $session->markAsCompleted(
                paymentId: $transaction->id,
                transactionId: $cliqRequest->request_id,
            );

            // Record bank offer redemption
            if ($session->bank_offer_id) {
                $this->discountService->recordRedemption(
                    bankOfferId: $session->bank_offer_id,
                    userId: $session->customer_id,
                    branchId: $session->branch_id,
                    originalAmount: $session->original_amount,
                    discountApplied: $session->discount_amount,
                );
            }

            // Broadcast events
            broadcast(new QrSessionCompleted($session))->toOthers();
            broadcast(new PaymentCompleted($transaction))->toOthers();
        }

        Log::channel('payment')->info('CliQ Payment Success', [
            'request_id' => $cliqRequest->request_id,
            'session_code' => $session?->session_code,
            'amount' => $cliqRequest->amount,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment recorded']);
    }

    /**
     * Handle failed CliQ payment
     */
    private function handleFailure(CliqPaymentRequest $cliqRequest, array $payload): JsonResponse
    {
        $reason = $payload['reason'] ?? $payload['ReasonCode'] ?? 'Payment rejected';

        $cliqRequest->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'response_data' => $payload,
        ]);

        // Revert QR session to allow retry
        $session = $cliqRequest->qrSession;
        if ($session && $session->status === 'processing') {
            $session->update(['status' => 'scanned']);
        }

        Log::channel('payment')->warning('CliQ Payment Failed', [
            'request_id' => $cliqRequest->request_id,
            'reason' => $reason,
        ]);

        return response()->json(['success' => true, 'message' => 'Failure recorded']);
    }

    /**
     * Handle expired CliQ payment
     */
    private function handleExpired(CliqPaymentRequest $cliqRequest, array $payload): JsonResponse
    {
        $cliqRequest->update([
            'status' => 'expired',
            'response_data' => $payload,
        ]);

        // Revert QR session to allow retry with different method
        $session = $cliqRequest->qrSession;
        if ($session && $session->status === 'processing') {
            $session->update(['status' => 'scanned']);
        }

        Log::channel('payment')->info('CliQ Payment Expired', [
            'request_id' => $cliqRequest->request_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Expiry recorded']);
    }

    /**
     * Verify JOPACC webhook signature
     */
    private function verifyJopaccSignature(Request $request): bool
    {
        $signature = $request->header('X-JOPACC-Signature')
            ?? $request->header('X-Signature')
            ?? $request->header('Authorization');

        $secret = config('payment.gateways.cliq.webhook_secret');

        if (empty($secret)) {
            // Skip verification if no secret configured (not recommended for production)
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        // JOPACC may use different signature methods
        // This is a basic HMAC verification - adjust based on actual JOPACC specs
        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $payload, $secret);

        // Try different signature formats
        if (hash_equals($computed, $signature)) {
            return true;
        }

        // Try base64 encoded signature
        if (hash_equals(base64_encode($computed), $signature)) {
            return true;
        }

        // Try without prefix
        $signatureParts = explode(' ', $signature);
        $signatureValue = end($signatureParts);
        if (hash_equals($computed, $signatureValue)) {
            return true;
        }

        return false;
    }
}
