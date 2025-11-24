<?php

namespace Modules\RetailerOnboarding\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\RetailerOnboarding\Services\OnboardingService;

class OnboardingController extends Controller
{
    /**
     * Get current onboarding status
     */
    public function status(OnboardingService $service): JsonResponse
    {
        try {
            $service
                ->setAuthUser(Auth::user())
                ->getOnboardingStatus()
                ->collectOutputs($data);

            return response()->json([
                'success' => true,
                'data' => $data
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
     * Start or resume onboarding
     */
    public function start(OnboardingService $service): JsonResponse
    {
        try {
            $service
                ->setAuthUser(Auth::user())
                ->getOrCreateOnboarding()
                ->collectOutput('onboarding', $onboarding);

            return response()->json([
                'success' => true,
                'data' => $onboarding
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
     * Step 1: Send phone verification OTP
     */
    public function sendPhoneOtp(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|phone:AUTO'
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->sendPhoneVerification();

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.code_sent')
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Step 1: Verify phone OTP
     */
    public function verifyPhone(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|phone:AUTO',
            'code' => 'required|string|size:6'
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->verifyPhone()
                ->collectOutput('onboarding', $onboarding);

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.verification_success'),
                'data' => $onboarding
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Step 2: Save payment methods
     */
    public function savePaymentMethods(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*.type' => 'required|in:cliq,bank',
            'payment_methods.*.cliq_number' => 'required_if:payment_methods.*.type,cliq|nullable|string',
            'payment_methods.*.bank_name' => 'required_if:payment_methods.*.type,bank|nullable|string',
            'payment_methods.*.iban' => 'required_if:payment_methods.*.type,bank|nullable|string',
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->savePaymentMethods();

            return response()->json([
                'success' => true,
                'message' => 'Payment methods saved successfully'
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
     * Step 2: Send Cliq verification OTP
     */
    public function sendCliqOtp(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'cliq_number' => 'required|string|phone:AUTO'
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->sendCliqVerification();

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.code_sent')
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Step 2: Verify Cliq OTP
     */
    public function verifyCliq(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'cliq_number' => 'required|string|phone:AUTO',
            'code' => 'required|string|size:6'
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->verifyCliq();

            return response()->json([
                'success' => true,
                'message' => __('otp::messages.verification_success')
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Step 2: Complete payment details step
     */
    public function completePaymentDetails(OnboardingService $service): JsonResponse
    {
        try {
            $service
                ->setAuthUser(Auth::user())
                ->completePaymentDetails()
                ->collectOutput('onboarding', $onboarding);

            return response()->json([
                'success' => true,
                'data' => $onboarding
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
     * Step 3: Save brand information
     */
    public function saveBrandInfo(Request $request, OnboardingService $service): JsonResponse
    {
        // Brand validation would be more complex in production
        $validated = $request->validate([
            'brand_type' => 'required|in:single,group',
            'brands' => 'required|array|min:1',
            'brands.*.name' => 'required|string|max:255',
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->saveBrandInformation()
                ->collectOutput('onboarding', $onboarding);

            return response()->json([
                'success' => true,
                'data' => $onboarding
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
     * Step 4: Get subscription plans
     */
    public function getPlans(OnboardingService $service): JsonResponse
    {
        try {
            $service
                ->getSubscriptionPlans()
                ->collectOutput('plans', $plans);

            return response()->json([
                'success' => true,
                'data' => $plans
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
     * Step 4: Select subscription plan
     */
    public function selectPlan(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->selectSubscription()
                ->collectOutputs($data);

            return response()->json([
                'success' => true,
                'data' => $data
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
     * Step 5: Process payment
     */
    public function processPayment(Request $request, OnboardingService $service): JsonResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:retailer_subscriptions,id',
            'payment_method' => 'required|in:cash,card,cliq',
            'discount_code' => 'nullable|string',
            'card_last_four' => 'required_if:payment_method,card|nullable|string|size:4',
        ]);

        try {
            $service
                ->setInputs($validated)
                ->setAuthUser(Auth::user())
                ->processPayment()
                ->collectOutputs($data);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $data
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
