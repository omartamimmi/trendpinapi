<?php

namespace Modules\RetailerOnboarding\Services;

use App\Abstractions\Service;
use Exception;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerPaymentMethod;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;
use Modules\Otp\Services\OtpService;

class OnboardingService extends Service
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Set authenticated user
     */
    public function setAuthUser($user): static
    {
        $this->setOutput('user', $user);
        return $this;
    }

    /**
     * Get or create onboarding session for user
     */
    public function getOrCreateOnboarding(): static
    {
        $this->collectOutput('user', $user);

        $onboarding = RetailerOnboarding::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'in_progress'],
            ['current_step' => 'retailer_details']
        );

        $this->setOutput('onboarding', $onboarding);
        return $this;
    }

    /**
     * Step 1: Send phone verification OTP
     */
    public function sendPhoneVerification(): static
    {
        $phoneNumber = $this->getInput('phone_number');
        $this->otpService->send($phoneNumber);
        return $this;
    }

    /**
     * Step 1: Verify phone OTP
     */
    public function verifyPhone(): static
    {
        $phoneNumber = $this->getInput('phone_number');
        $code = $this->getInput('code');

        $this->otpService->verify($phoneNumber, $code);

        $this->collectOutput('user', $user);

        // Get or create onboarding record
        $onboarding = RetailerOnboarding::firstOrCreate(
            ['user_id' => $user->id],
            ['current_step' => 'retailer_details', 'status' => 'in_progress']
        );

        // Update to payment_details step
        $onboarding->update([
            'phone_verified' => true,
            'current_step' => 'payment_details',
            'status' => 'in_progress'
        ]);
        $onboarding->markStepCompleted('retailer_details');

        // Update user phone
        $user->update(['phone' => $phoneNumber]);

        $this->setOutput('onboarding', $onboarding);
        return $this;
    }

    /**
     * Step 2: Save payment methods (Cliq/Bank)
     */
    public function savePaymentMethods(): static
    {
        $this->collectOutput('user', $user);
        $paymentMethods = $this->getInput('payment_methods');

        foreach ($paymentMethods as $method) {
            RetailerPaymentMethod::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $method['type']
                ],
                [
                    'cliq_number' => $method['cliq_number'] ?? null,
                    'bank_name' => $method['bank_name'] ?? null,
                    'iban' => $method['iban'] ?? null,
                    'is_primary' => $method['is_primary'] ?? false,
                ]
            );
        }

        return $this;
    }

    /**
     * Step 2: Send Cliq verification OTP
     */
    public function sendCliqVerification(): static
    {
        $cliqNumber = $this->getInput('cliq_number');
        $this->otpService->send($cliqNumber);
        return $this;
    }

    /**
     * Step 2: Verify Cliq OTP
     */
    public function verifyCliq(): static
    {
        $cliqNumber = $this->getInput('cliq_number');
        $code = $this->getInput('code');

        $this->otpService->verify($cliqNumber, $code);

        $this->collectOutput('user', $user);

        // Update Cliq payment method as verified
        RetailerPaymentMethod::where('user_id', $user->id)
            ->where('type', 'cliq')
            ->where('cliq_number', $cliqNumber)
            ->update(['cliq_verified' => true]);

        // Update onboarding
        $onboarding = RetailerOnboarding::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($onboarding) {
            $onboarding->update(['cliq_verified' => true]);
        }

        return $this;
    }

    /**
     * Step 2: Complete payment details step
     */
    public function completePaymentDetails(): static
    {
        $this->collectOutput('user', $user);

        $onboarding = RetailerOnboarding::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($onboarding) {
            $onboarding->update(['current_step' => 'brand_information']);
            $onboarding->markStepCompleted('payment_details');
        }

        $this->setOutput('onboarding', $onboarding);
        return $this;
    }

    /**
     * Step 3: Save brand information
     * Note: This uses existing Brand/Shop module functionality
     */
    public function saveBrandInformation(): static
    {
        $this->collectOutput('user', $user);
        $brands = $this->getInput('brands');
        $brandType = $this->getInput('brand_type');

        // Delete existing brands for this user first to avoid duplicates
        if (!empty($brands)) {
            \Modules\Business\app\Models\Brand::where('create_user', $user->id)->delete();

            // Create fresh brand records
            foreach ($brands as $brandData) {
                \Modules\Business\app\Models\Brand::create([
                    'name' => $brandData['name'],
                    'description' => $brandData['description'] ?? null,
                    'location' => json_encode([
                        'lat' => $brandData['latitude'] ?? 0,
                        'lng' => $brandData['longitude'] ?? 0,
                    ]),
                    'create_user' => $user->id,
                    'type' => $brandType === 'group' ? 'group' : 'single',
                ]);
            }
        }

        $onboarding = RetailerOnboarding::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($onboarding) {
            $onboarding->update(['current_step' => 'subscription']);
            $onboarding->markStepCompleted('brand_information');
        }

        $this->setOutput('onboarding', $onboarding);
        return $this;
    }

    /**
     * Step 4: Get available subscription plans
     */
    public function getSubscriptionPlans(): static
    {
        $plans = SubscriptionPlan::active()->get();
        $this->setOutput('plans', $plans);
        return $this;
    }

    /**
     * Step 4: Select subscription plan
     */
    public function selectSubscription(): static
    {
        $this->collectOutput('user', $user);
        $planId = $this->getInput('plan_id');

        $plan = SubscriptionPlan::findOrFail($planId);

        // Update or create subscription (avoid duplicates when editing)
        $startsAt = now();
        $endsAt = $startsAt->copy()->addMonths($plan->duration_months);
        $trialEndsAt = $plan->trial_days > 0 ? $startsAt->copy()->addDays($plan->trial_days) : null;

        $subscription = RetailerSubscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_plan_id' => $plan->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
                'status' => 'pending',
            ]
        );

        // Update onboarding
        $onboarding = RetailerOnboarding::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        if ($onboarding) {
            $onboarding->update(['current_step' => 'payment']);
            $onboarding->markStepCompleted('subscription');
        }

        $this->setOutput('subscription', $subscription);
        $this->setOutput('onboarding', $onboarding);
        return $this;
    }

    /**
     * Step 5: Process payment
     */
    public function processPayment(): static
    {
        $this->collectOutput('user', $user);

        $subscriptionId = $this->getInput('subscription_id');
        $paymentMethod = $this->getInput('payment_method');
        $discountCode = $this->getInput('discount_code');

        $subscription = RetailerSubscription::with('plan')->findOrFail($subscriptionId);
        $plan = $subscription->plan;

        // Calculate amounts
        $amount = $plan->price;
        $discount = 0;

        // Apply discount code if provided
        if ($discountCode) {
            // Discount logic here - for now assume 50% discount for demo
            if ($discountCode === 'ABCD50%') {
                $discount = $amount * 0.5;
            }
        }

        $subtotal = $amount - $discount;
        $total = $subtotal;

        // Create payment record
        $payment = SubscriptionPayment::create([
            'retailer_subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => $total,
            'discount_code' => $discountCode,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'card_last_four' => $this->getInput('card_last_four'),
        ]);

        // Process payment based on method
        if ($paymentMethod === 'cash') {
            // Cash payment - mark as pending for manual confirmation
            $payment->update(['status' => 'pending']);
        } elseif ($paymentMethod === 'card') {
            // Card payment - integrate with payment gateway
            // For now, simulate success
            $payment->update([
                'status' => 'completed',
                'transaction_id' => 'TXN_' . uniqid(),
            ]);
        } elseif ($paymentMethod === 'cliq') {
            // Cliq payment - similar to cash
            $payment->update(['status' => 'pending']);
        }

        // If payment completed, activate subscription
        if ($payment->status === 'completed') {
            $subscription->update(['status' => 'active']);

            // Complete onboarding
            $onboarding = RetailerOnboarding::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->first();

            if ($onboarding) {
                $onboarding->update([
                    'current_step' => 'completed',
                    'status' => 'completed',
                    'approval_status' => 'pending_approval'
                ]);
                $onboarding->markStepCompleted('payment');
            }
        }

        $this->setOutput('payment', $payment);
        $this->setOutput('subscription', $subscription);
        return $this;
    }

    /**
     * Get current onboarding status
     */
    public function getOnboardingStatus(): static
    {
        $this->collectOutput('user', $user);

        $onboarding = RetailerOnboarding::where('user_id', $user->id)
            ->latest()
            ->first();

        $paymentMethods = RetailerPaymentMethod::where('user_id', $user->id)->get();
        $subscription = RetailerSubscription::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->first();

        $this->setOutput('onboarding', $onboarding);
        $this->setOutput('payment_methods', $paymentMethods);
        $this->setOutput('subscription', $subscription);

        return $this;
    }
}
