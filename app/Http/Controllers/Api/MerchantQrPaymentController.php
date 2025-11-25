<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MerchantQrPaymentController extends Controller
{
    protected QrPaymentService $qrPaymentService;

    public function __construct(QrPaymentService $qrPaymentService)
    {
        $this->qrPaymentService = $qrPaymentService;
    }

    /**
     * Generate QR code for payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:500',
            'expiry_minutes' => 'nullable|integer|min:1|max:1440', // Max 24 hours
            'metadata' => 'nullable|array',
        ]);

        try {
            $merchant = Auth::user();

            $qrPayment = $this->qrPaymentService->generateQrCode(
                merchant: $merchant,
                amount: $validated['amount'],
                description: $validated['description'] ?? null,
                expiryMinutes: $validated['expiry_minutes'] ?? 15,
                metadata: $validated['metadata'] ?? []
            );

            // Get QR code as base64
            $qrCodeImage = $this->qrPaymentService->getQrCodeBase64($qrPayment);

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'data' => [
                    'id' => $qrPayment->id,
                    'reference' => $qrPayment->qr_code_reference,
                    'amount' => $qrPayment->amount,
                    'currency' => $qrPayment->currency,
                    'description' => $qrPayment->description,
                    'status' => $qrPayment->status,
                    'expires_at' => $qrPayment->expires_at->toIso8601String(),
                    'qr_code_image' => $qrCodeImage,
                    'qr_code_data' => $qrPayment->qr_code_data,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get merchant's QR payments list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $perPage = $request->query('per_page', 20);

        $payments = $this->qrPaymentService->getMerchantPayments(
            Auth::user(),
            $status,
            min($perPage, 100) // Max 100 per page
        );

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get specific QR payment details
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $qrPayment = Auth::user()
            ->qrPaymentsAsMerchant()
            ->with('customer')
            ->find($id);

        if (!$qrPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // Get QR code image if pending
        $qrCodeImage = null;
        if ($qrPayment->status === 'pending') {
            $qrCodeImage = $this->qrPaymentService->getQrCodeBase64($qrPayment);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $qrPayment,
                'qr_code_image' => $qrCodeImage,
            ],
        ]);
    }

    /**
     * Cancel QR payment
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        $qrPayment = Auth::user()
            ->qrPaymentsAsMerchant()
            ->find($id);

        if (!$qrPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($this->qrPaymentService->cancelPayment($qrPayment)) {
            return response()->json([
                'success' => true,
                'message' => 'Payment cancelled successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment cannot be cancelled',
        ], 400);
    }

    /**
     * Check payment status
     *
     * @param string $reference
     * @return JsonResponse
     */
    public function checkStatus(string $reference): JsonResponse
    {
        $qrPayment = $this->qrPaymentService->getByReference($reference);

        if (!$qrPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // Verify the merchant owns this QR payment
        if ($qrPayment->merchant_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'reference' => $qrPayment->qr_code_reference,
                'status' => $qrPayment->status,
                'amount' => $qrPayment->amount,
                'paid_at' => $qrPayment->paid_at?->toIso8601String(),
                'customer' => $qrPayment->customer ? [
                    'id' => $qrPayment->customer->id,
                    'name' => $qrPayment->customer->name,
                ] : null,
            ],
        ]);
    }
}
