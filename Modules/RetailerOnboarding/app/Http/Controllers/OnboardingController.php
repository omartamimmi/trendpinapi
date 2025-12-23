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
    public function savePaymentMethods(Request $request, OnboardingService $service)
    {
        $validated = $request->validate([
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*.type' => 'required|in:cliq,bank',
            'payment_methods.*.cliq_number' => 'required_if:payment_methods.*.type,cliq|nullable|string',
            'payment_methods.*.bank_name' => 'required_if:payment_methods.*.type,bank|nullable|string',
            'payment_methods.*.iban' => 'required_if:payment_methods.*.type,bank|nullable|string',
            'onboarding_user_id' => 'nullable|integer|exists:users,id',
        ]);

        try {
            // If onboarding_user_id is provided (admin edit), use that user, otherwise use Auth user
            $user = $request->has('onboarding_user_id')
                ? \App\Models\User::find($validated['onboarding_user_id'])
                : Auth::user();

            $service
                ->setInputs($validated)
                ->setAuthUser($user)
                ->savePaymentMethods();

            // Update current step
            $onboarding = $user->retailerOnboarding;
            if ($onboarding) {
                $onboarding->update(['current_step' => 'brand_information']);
            }

            // Check if this is an admin editing (via onboarding_user_id parameter)
            if ($request->has('onboarding_user_id') && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.onboarding-approvals.edit', ['id' => $onboarding->id]);
            }

            // If editing (rejected/changes_requested), preserve edit parameter
            if ($onboarding && in_array($onboarding->approval_status, ['rejected', 'changes_requested'])) {
                return redirect()->route('retailer.onboarding', ['edit' => 1]);
            }

            return back();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
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
    public function saveBrandInfo(Request $request, OnboardingService $service)
    {
        // Brand validation would be more complex in production
        $validated = $request->validate([
            'brand_type' => 'required|in:single,group',
            'brands' => 'required|array|min:1',
            'brands.*.name' => 'required|string|max:255',
            'onboarding_user_id' => 'nullable|integer|exists:users,id',
        ]);

        try {
            // If onboarding_user_id is provided (admin edit), use that user, otherwise use Auth user
            $user = $request->has('onboarding_user_id')
                ? \App\Models\User::find($validated['onboarding_user_id'])
                : Auth::user();

            $service
                ->setInputs($validated)
                ->setAuthUser($user)
                ->saveBrandInformation()
                ->collectOutput('onboarding', $onboarding);

            // Update current step
            $onboarding = $user->retailerOnboarding;
            if ($onboarding) {
                $onboarding->update(['current_step' => 'subscription']);
            }

            // Check if this is an admin editing (via onboarding_user_id parameter)
            if ($request->has('onboarding_user_id') && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.onboarding-approvals.edit', ['id' => $onboarding->id]);
            }

            // If editing (rejected/changes_requested), preserve edit parameter
            if ($onboarding && in_array($onboarding->approval_status, ['rejected', 'changes_requested'])) {
                return redirect()->route('retailer.onboarding', ['edit' => 1]);
            }

            return back();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
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
    public function selectPlan(Request $request, OnboardingService $service)
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'onboarding_user_id' => 'nullable|integer|exists:users,id',
        ]);

        try {
            // If onboarding_user_id is provided (admin edit), use that user, otherwise use Auth user
            $user = $request->has('onboarding_user_id')
                ? \App\Models\User::find($validated['onboarding_user_id'])
                : Auth::user();

            // Only process if plan_id is provided
            if (!empty($validated['plan_id'])) {
                $service
                    ->setInputs($validated)
                    ->setAuthUser($user)
                    ->selectSubscription()
                    ->collectOutputs($data);
            }

            // Update current step
            $onboarding = $user->retailerOnboarding;
            if ($onboarding) {
                $onboarding->update(['current_step' => 'payment']);
            }

            // Check if this is an admin editing (via onboarding_user_id parameter)
            if ($request->has('onboarding_user_id') && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.onboarding-approvals.edit', ['id' => $onboarding->id]);
            }

            // If editing (rejected/changes_requested), preserve edit parameter
            if ($onboarding && in_array($onboarding->approval_status, ['rejected', 'changes_requested'])) {
                return redirect()->route('retailer.onboarding', ['edit' => 1]);
            }

            return back();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
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

    /**
     * Save Step 1 - Retailer Details
     */
    public function saveRetailerDetails(Request $request)
    {
        \Log::info('saveRetailerDetails called with data:', $request->all());

        $validated = $request->validate([
            'retailer_name' => 'required|string|max:255',
            'category' => 'required|string',
            'city' => 'required|string',
            'phone_number' => 'nullable|string',
            'country_code' => 'nullable|string',
            'logo' => 'nullable|file|image|max:5120',
            'license' => 'nullable|file|max:10240',
            'onboarding_user_id' => 'nullable|integer|exists:users,id',
        ]);

        \Log::info('Validated data:', $validated);

        try {
            // If onboarding_user_id is provided (admin edit), use that user, otherwise use Auth user
            $user = $request->has('onboarding_user_id')
                ? \App\Models\User::find($validated['onboarding_user_id'])
                : Auth::user();
            $onboarding = $user->retailerOnboarding;

            if (!$onboarding) {
                $onboarding = \Modules\RetailerOnboarding\app\Models\RetailerOnboarding::create([
                    'user_id' => $user->id,
                    'current_step' => 'retailer_details',
                    'status' => 'in_progress'
                ]);
            }

            // Store files if uploaded
            $logoPath = null;
            $licensePath = null;

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('retailer/logos', 'public');
            }

            if ($request->hasFile('license')) {
                $licensePath = $request->file('license')->store('retailer/licenses', 'public');
            }

            // Update user with retailer info
            $user->update([
                'name' => $validated['retailer_name'],
                'phone' => $validated['phone_number'] ?? $user->phone,
            ]);

            // Store Step 1 data in completed_steps JSON
            $completedSteps = $onboarding->completed_steps ?? [];
            if (!in_array('retailer_details', $completedSteps)) {
                $completedSteps[] = 'retailer_details';
            }

            $updateData = [
                'current_step' => 'payment_details', // Move to next step
                'completed_steps' => $completedSteps,
                'city' => $validated['city'],
                'category' => $validated['category'],
                'logo_path' => $logoPath,
                'license_path' => $licensePath,
            ];

            \Log::info('Updating onboarding with data:', $updateData);

            $onboarding->update($updateData);

            \Log::info('Onboarding updated successfully. New values:', [
                'city' => $onboarding->city,
                'category' => $onboarding->category,
                'logo_path' => $onboarding->logo_path,
                'license_path' => $onboarding->license_path,
            ]);

            // Check if this is an admin editing (via onboarding_user_id parameter)
            if ($request->has('onboarding_user_id') && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.onboarding-approvals.edit', ['id' => $onboarding->id]);
            }

            // If editing (rejected/changes_requested), preserve edit parameter
            if (in_array($onboarding->approval_status, ['rejected', 'changes_requested'])) {
                return redirect()->route('retailer.onboarding', ['edit' => 1]);
            }

            // Return back to allow frontend to handle navigation
            return back();
        } catch (\Exception $e) {
            \Log::error('Retailer details save error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to save retailer details. Please try again.']);
        }
    }

    /**
     * Complete onboarding - Submit all data at once
     * Set status as pending for admin approval
     */
    public function completeOnboarding(Request $request)
    {
        // dd($request);
        // Simple validation - just payment option
        $validated = $request->validate([
            'payment_option' => 'nullable|string',
            'onboarding_user_id' => 'nullable|integer|exists:users,id',
        ]);

        try {
            // If onboarding_user_id is provided (admin edit), use that user, otherwise use Auth user
            $user = $request->has('onboarding_user_id')
                ? \App\Models\User::find($validated['onboarding_user_id'])
                : Auth::user();

            $onboarding = $user->retailerOnboarding;

            if (!$onboarding) {
                return back()->withErrors(['error' => 'No onboarding record found. Please start from Step 1.']);
            }

            // Update onboarding status to completed (waiting for admin approval)
            $onboarding->update([
                'current_step' => 'completed',
                'status' => 'completed', // Mark as completed
                'approval_status' => 'pending', // Waiting for admin approval
                'completed_steps' => [
                    'retailer_details',
                    'payment_details',
                    'brand_information',
                    'subscription',
                    'payment'
                ],
            ]);

            // Check if this is an admin editing (via onboarding_user_id parameter)
            if ($request->has('onboarding_user_id') && Auth::user()->hasRole('admin')) {
                return redirect()->route('admin.onboarding-approvals.edit', ['id' => $onboarding->id])
                    ->with('success', 'Retailer onboarding updated successfully!');
            }

            // All data is already saved in previous steps, just redirect
            return redirect('/retailer/onboarding')->with('success', 'Application submitted successfully! Awaiting admin approval.');
        } catch (Exception $e) {
            Log::error('Onboarding completion error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withErrors([
                'error' => 'Failed to complete onboarding: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
