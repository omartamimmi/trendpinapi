<?php

namespace Modules\RetailerOnboarding\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;

class RetailerOnboardingController extends Controller
{
    /**
     * List all retailer onboardings
     */
    public function index(Request $request): JsonResponse
    {
        $query = RetailerOnboarding::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $onboardings = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $onboardings
        ]);
    }

    /**
     * Get specific retailer onboarding details
     */
    public function show(int $id): JsonResponse
    {
        $onboarding = RetailerOnboarding::with(['user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $onboarding
        ]);
    }

    /**
     * List all subscriptions
     */
    public function subscriptions(Request $request): JsonResponse
    {
        $query = RetailerSubscription::with(['user', 'plan', 'payments']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * List all payments
     */
    public function payments(Request $request): JsonResponse
    {
        $query = SubscriptionPayment::with(['user', 'subscription.plan']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Approve a pending payment (cash/cliq)
     */
    public function approvePayment(int $paymentId): JsonResponse
    {
        try {
            $payment = SubscriptionPayment::findOrFail($paymentId);

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not pending'
                ], 422);
            }

            $payment->update([
                'status' => 'completed',
                'transaction_id' => 'ADMIN_' . uniqid(),
            ]);

            // Activate subscription
            $payment->subscription->update(['status' => 'active']);

            // Complete onboarding
            $onboarding = RetailerOnboarding::where('user_id', $payment->user_id)
                ->where('status', 'in_progress')
                ->first();

            if ($onboarding) {
                $onboarding->update([
                    'current_step' => 'completed',
                    'status' => 'completed'
                ]);
                $onboarding->markStepCompleted('payment');
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully',
                'data' => $payment
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a pending payment
     */
    public function rejectPayment(int $paymentId): JsonResponse
    {
        try {
            $payment = SubscriptionPayment::findOrFail($paymentId);

            if ($payment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment is not pending'
                ], 422);
            }

            $payment->update(['status' => 'failed']);

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected',
                'data' => $payment
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
