<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomerQrPaymentController extends Controller
{
    protected QrPaymentService $qrPaymentService;

    public function __construct(QrPaymentService $qrPaymentService)
    {
        $this->qrPaymentService = $qrPaymentService;
    }

    /**
     * Verify QR code data (before payment)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Decode QR data
            $qrData = $this->qrPaymentService->verifyQrData($validated['qr_data']);

            if (!$qrData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code',
                ], 400);
            }

            // Get payment from database
            $qrPayment = $this->qrPaymentService->getByReference($qrData['reference']);

            if (!$qrPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                ], 404);
            }

            // Check if expired
            if ($qrPayment->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This QR code has expired',
                ], 400);
            }

            // Check if already paid
            if ($qrPayment->isCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment has already been completed',
                ], 400);
            }

            // Return payment details for confirmation
            return response()->json([
                'success' => true,
                'data' => [
                    'reference' => $qrPayment->qr_code_reference,
                    'merchant' => [
                        'id' => $qrPayment->merchant->id,
                        'name' => $qrPayment->merchant->name,
                    ],
                    'amount' => $qrPayment->amount,
                    'currency' => $qrPayment->currency,
                    'description' => $qrPayment->description,
                    'expires_at' => $qrPayment->expires_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify QR code: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'payment_method' => 'nullable|string|in:wallet,card,bank', // For future integration
        ]);

        try {
            $customer = Auth::user();
            $result = $this->qrPaymentService->processPayment(
                $validated['reference'],
                $customer
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'payment' => $result['payment'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer's payment history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);

        $payments = $this->qrPaymentService->getCustomerPayments(
            Auth::user(),
            min($perPage, 100) // Max 100 per page
        );

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get payment details by reference
     *
     * @param string $reference
     * @return JsonResponse
     */
    public function show(string $reference): JsonResponse
    {
        $qrPayment = $this->qrPaymentService->getByReference($reference);

        if (!$qrPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // Only show to customer who paid or if still pending
        if ($qrPayment->customer_id !== Auth::id() && $qrPayment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reference' => $qrPayment->qr_code_reference,
                'merchant' => [
                    'name' => $qrPayment->merchant->name,
                ],
                'amount' => $qrPayment->amount,
                'currency' => $qrPayment->currency,
                'description' => $qrPayment->description,
                'status' => $qrPayment->status,
                'paid_at' => $qrPayment->paid_at?->toIso8601String(),
            ],
        ]);
    }
}
