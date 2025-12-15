<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\app\Http\Requests\ApproveOnboardingRequest;
use Modules\Admin\app\Http\Requests\RejectOnboardingRequest;
use Modules\Admin\app\Services\Contracts\OnboardingServiceInterface;
use Modules\Business\app\Models\Brand;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerPaymentMethod;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class AdminOnboardingController extends Controller
{
    public function __construct(
        protected OnboardingServiceInterface $onboardingService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('search');

        $onboardings = $this->onboardingService->getOnboardings($status, $search);
        $counts = $this->onboardingService->getCounts();

        return Inertia::render('Admin/OnboardingApprovals', [
            'onboardings' => $onboardings,
            'currentStatus' => $status,
            'counts' => $counts,
        ]);
    }

    public function show(int $id): Response
    {
        $data = $this->onboardingService->getOnboardingDetails($id);

        return Inertia::render('Admin/OnboardingReview', $data);
    }

    public function edit(int $id): Response
    {
        $onboarding = RetailerOnboarding::with(['user'])->findOrFail($id);
        $user = $onboarding->user;

        $stepMap = [
            'retailer_details' => 1,
            'payment_details' => 2,
            'brand_information' => 3,
            'subscription' => 4,
            'payment' => 5,
            'completed' => 5
        ];

        $plans = SubscriptionPlan::where('is_active', true)->get();
        $paymentMethods = RetailerPaymentMethod::where('user_id', $user->id)->get();
        $brands = Brand::where('create_user', $user->id)->get();
        $subscription = RetailerSubscription::where('user_id', $user->id)->first();

        $onboardingData = $onboarding->toArray();
        $onboardingData['logo_url'] = $onboarding->logo_path
            ? asset('storage/' . $onboarding->logo_path)
            : null;
        $onboardingData['license_url'] = $onboarding->license_path
            ? asset('storage/' . $onboarding->license_path)
            : null;

        return Inertia::render('Admin/EditOnboarding', [
            'onboarding' => $onboardingData,
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
            ],
            'currentStep' => $stepMap[$onboarding->current_step] ?? 1,
            'plans' => $plans,
            'existingPaymentMethods' => $paymentMethods,
            'existingBrands' => $brands,
            'existingSubscription' => $subscription,
        ]);
    }

    public function approve(ApproveOnboardingRequest $request, int $id): RedirectResponse
    {
        $this->onboardingService->approve($id, $request->get('admin_notes'));

        return redirect()->back()->with('success', 'Onboarding approved successfully');
    }

    public function requestChanges(RejectOnboardingRequest $request, int $id): RedirectResponse
    {
        $this->onboardingService->requestChanges($id, $request->validated('admin_notes'));

        return redirect()->back()->with('success', 'Changes requested successfully');
    }

    public function reject(RejectOnboardingRequest $request, int $id): RedirectResponse
    {
        $this->onboardingService->reject($id, $request->validated('admin_notes'));

        return redirect()->back()->with('success', 'Onboarding rejected');
    }
}
