<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrPayment;
use App\Services\QrPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MerchantQrPaymentController extends Controller
{
    public function __construct(
        protected QrPaymentService $qrPaymentService
    ) {}

    /**
     * Generate QR code
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'description' => 'nullable|string|max:500',
            'expiry_minutes' => 'nullable|integer|min:1|max:1440',
            'metadata' => 'nullable|array',
        ]);

        try {
            $qrPayment = $this->qrPaymentService->generateQrCode(
                branchId: $validated['branch_id'],
                user: Auth::user(),
                amount: $validated['amount'],
                description: $validated['description'] ?? null,
                expiryMinutes: $validated['expiry_minutes'] ?? 15,
                metadata: $validated['metadata'] ?? []
            );

            $qrCodeImage = $this->qrPaymentService->getQrCodeBase64($qrPayment);

            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully',
                'data' => [
                    'id' => $qrPayment->id,
                    'reference' => $qrPayment->qr_code_reference,
                    'branch' => [
                        'id' => $qrPayment->branch->id,
                        'name' => $qrPayment->branch->name,
                    ],
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
                'message' => 'Failed to generate QR code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment list for a branch
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'nullable|in:pending,completed,expired,cancelled',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = QrPayment::with(['branch', 'user', 'customer'])
            ->where('user_id', Auth::id());

        if (isset($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($validated['per_page'] ?? 20);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get single payment details
     */
    public function show($id): JsonResponse
    {
        $qrPayment = QrPayment::with(['branch', 'user', 'customer'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $qrCodeImage = $this->qrPaymentService->getQrCodeBase64($qrPayment);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $qrPayment->id,
                'reference' => $qrPayment->qr_code_reference,
                'branch' => [
                    'id' => $qrPayment->branch->id,
                    'name' => $qrPayment->branch->name,
                ],
                'amount' => $qrPayment->amount,
                'currency' => $qrPayment->currency,
                'description' => $qrPayment->description,
                'status' => $qrPayment->status,
                'expires_at' => $qrPayment->expires_at->toIso8601String(),
                'paid_at' => $qrPayment->paid_at?->toIso8601String(),
                'qr_code_image' => $qrCodeImage,
                'qr_code_data' => $qrPayment->qr_code_data,
                'customer' => $qrPayment->customer ? [
                    'id' => $qrPayment->customer->id,
                    'name' => $qrPayment->customer->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel($id): JsonResponse
    {
        $qrPayment = QrPayment::where('user_id', Auth::id())->findOrFail($id);

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
     */
    public function checkStatus($reference): JsonResponse
    {
        $qrPayment = $this->qrPaymentService->getByReference($reference);

        if (!$qrPayment || $qrPayment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
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
