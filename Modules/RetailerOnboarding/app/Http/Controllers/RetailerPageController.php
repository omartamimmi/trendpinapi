<?php

namespace Modules\RetailerOnboarding\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
use Modules\RetailerOnboarding\app\Models\Offer;
use Modules\Notification\app\Services\AsyncNotificationService;

class RetailerPageController extends Controller
{
    /**
     * Show the login page
     */
    public function loginPage()
    {
        return Inertia::render('Retailer/Login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user has retailer role
            if (!$user->hasRole('retailer')) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'This account is not a retailer account.',
                ]);
            }

            // Check onboarding status
            $onboarding = $user->retailerOnboarding;
            if (!$onboarding || $onboarding->status !== 'completed') {
                return redirect('/retailer/onboarding');
            }

            return redirect('/retailer/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show the registration page
     */
    public function registerPage()
    {
        return Inertia::render('Retailer/Register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('retailer');

        // Queue welcome notification to the new retailer (non-blocking)
        AsyncNotificationService::sendNewRetailerNotification($user);

        Auth::login($user);

        return redirect('/retailer/onboarding');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show the onboarding page
     */
    public function onboarding(Request $request)
    {
        $user = Auth::user();

        // Get or create onboarding record
        $onboarding = $user->retailerOnboarding;
        if (!$onboarding) {
            $onboarding = \Modules\RetailerOnboarding\app\Models\RetailerOnboarding::create([
                'user_id' => $user->id,
                'current_step' => 'retailer_details',
                'status' => 'in_progress'
            ]);
        }

        // If approved, redirect to dashboard
        if ($onboarding->approval_status === 'approved') {
            return redirect('/retailer/dashboard');
        }

        // If changes requested or rejected and user clicked edit, allow editing
        if (in_array($onboarding->approval_status, ['changes_requested', 'rejected']) && $request->has('edit')) {
            // Reset status to allow editing
            $onboarding->update(['status' => 'in_progress']);
            // Continue to show the onboarding form below
        }
        // If awaiting approval (pending/changes/rejected) and status is completed, show pending page
        elseif (in_array($onboarding->approval_status, ['pending', 'pending_approval', 'changes_requested', 'rejected']) && $onboarding->status === 'completed') {
            return Inertia::render('Retailer/PendingApproval', [
                'status' => $onboarding->approval_status,
                'admin_notes' => $onboarding->admin_notes,
            ]);
        }

        // Map step names to numbers
        $stepMap = [
            'retailer_details' => 1,
            'payment_details' => 2,
            'brand_information' => 3,
            'subscription' => 4,
            'payment' => 5,
            'completed' => 5
        ];

        $plans = SubscriptionPlan::where('is_active', true)->get();

        // Load related data for editing
        $paymentMethods = \Modules\RetailerOnboarding\app\Models\RetailerPaymentMethod::where('user_id', $user->id)->get();
        $brands = Brand::where('create_user', $user->id)->get();
        $subscription = RetailerSubscription::where('user_id', $user->id)->first();

        // Add full image URLs for logo and license
        $onboardingData = $onboarding->toArray();
        $onboardingData['logo_url'] = $onboarding->logo_path
            ? asset('storage/' . $onboarding->logo_path)
            : null;
        $onboardingData['license_url'] = $onboarding->license_path
            ? asset('storage/' . $onboarding->license_path)
            : null;

        return Inertia::render('Retailer/Onboarding', [
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

    /**
     * Show the dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get offers for the user
        $offers = Offer::where('user_id', $user->id)->get();
        $totalOffers = $offers->count();
        $activeOffers = $offers->where('status', 'active')->count();
        $totalClaims = $offers->sum('claims_count');
        $totalViews = $offers->sum('views_count');

        // Get recent offers (last 5)
        $recentOffers = Offer::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'name' => $offer->name,
                    'type' => $offer->discount_type,
                    'claims' => $offer->claims_count ?? 0,
                    'status' => $offer->status,
                ];
            });

        // Get subscription info
        $subscription = RetailerSubscription::where('user_id', $user->id)
            ->with('plan')
            ->where('status', 'active')
            ->latest()
            ->first();

        $stats = [
            'offers' => [
                'total' => $totalOffers,
                'active' => $activeOffers,
            ],
            'claims' => [
                'total' => $totalClaims,
                'this_month' => $totalClaims, // TODO: Filter by month if needed
            ],
            'views' => [
                'total' => $totalViews,
                'this_week' => $totalViews, // TODO: Filter by week if needed
            ],
            'subscription' => [
                'status' => $subscription ? ucfirst($subscription->status) : 'No Subscription',
                'expires_at' => $subscription && $subscription->ends_at
                    ? $subscription->ends_at->format('M d, Y')
                    : 'N/A',
            ],
            'recent_offers' => $recentOffers
        ];

        return Inertia::render('Retailer/Dashboard', [
            'stats' => $stats
        ]);
    }

    /**
     * Show settings page
     */
    public function settings()
    {
        $user = Auth::user();
        $onboarding = $user->retailerOnboarding;
        $subscription = RetailerSubscription::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->first();

        return Inertia::render('Retailer/Settings', [
            'user' => $user,
            'onboarding' => $onboarding,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Update profile settings
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }

    /**
     * Show brands page
     */
    public function brands()
    {
        $user = Auth::user();
        $brands = Brand::where('create_user', $user->id)
            ->with(['branches'])
            ->paginate(12);

        return Inertia::render('Retailer/Brands', [
            'brands' => $brands,
        ]);
    }

    /**
     * Show create brand page
     */
    public function createBrand()
    {
        $categories = \Modules\Category\Models\Category::where('status', 'published')->get();

        return Inertia::render('Retailer/BrandCreate', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store new brand
     */
    public function storeBrand(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'location' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'website_link' => 'nullable|string',
            'insta_link' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'status' => 'nullable|string|in:draft,publish',
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'title_ar' => $validated['title_ar'] ?? null,
            'description' => $validated['description'] ?? null,
            'description_ar' => $validated['description_ar'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'location' => $validated['location'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'website_link' => $validated['website_link'] ?? null,
            'insta_link' => $validated['insta_link'] ?? null,
            'facebook_link' => $validated['facebook_link'] ?? null,
            'create_user' => $user->id,
            'status' => $validated['status'] ?? 'draft',
        ]);

        // Sync categories
        if (!empty($validated['category_ids'])) {
            $brand->categories()->sync($validated['category_ids']);
        }

        // Create branches if provided
        if ($request->has('branches')) {
            foreach ($request->branches as $branchData) {
                if (!empty($branchData['name']) || !empty($branchData['location'])) {
                    Branch::create([
                        'brand_id' => $brand->id,
                        'name' => $branchData['name'] ?? 'Branch',
                        'location' => $branchData['location'] ?? null,
                        'lat' => $branchData['lat'] ?? null,
                        'lng' => $branchData['lng'] ?? null,
                        'status' => $branchData['status'] ?? 'draft',
                    ]);
                }
            }
        }

        return redirect('/retailer/brands')->with('success', 'Brand created successfully');
    }

    /**
     * Show edit brand page
     */
    public function editBrand(int $id)
    {
        $user = Auth::user();
        $brand = Brand::where('id', $id)
            ->where('create_user', $user->id)
            ->with(['branches', 'categories'])
            ->firstOrFail();

        $categories = \Modules\Category\Models\Category::where('status', 'published')->get();

        return Inertia::render('Retailer/BrandEdit', [
            'brand' => $brand,
            'categories' => $categories,
        ]);
    }

    /**
     * Update brand
     */
    public function updateBrand(Request $request, int $id)
    {
        $user = Auth::user();
        $brand = Brand::where('id', $id)
            ->where('create_user', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'group_id' => 'nullable',
            'phone_number' => 'nullable|string',
            'location' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'website_link' => 'nullable|string',
            'insta_link' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
            'status' => 'nullable|string|in:draft,publish',
        ]);

        // Handle empty group_id
        if (empty($validated['group_id'])) {
            unset($validated['group_id']);
        }

        // Remove category_ids from validated array before update
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $brand->update($validated);

        // Sync categories
        $brand->categories()->sync($categoryIds);

        // Update branches if provided
        if ($request->has('branches')) {
            Branch::where('brand_id', $brand->id)->delete();
            foreach ($request->branches as $branchData) {
                if (!empty($branchData['name']) || !empty($branchData['location'])) {
                    Branch::create([
                        'brand_id' => $brand->id,
                        'name' => $branchData['name'] ?? 'Branch',
                        'location' => $branchData['location'] ?? null,
                        'lat' => $branchData['lat'] ?? null,
                        'lng' => $branchData['lng'] ?? null,
                        'status' => $branchData['status'] ?? 'draft',
                    ]);
                }
            }
        }

        return redirect('/retailer/brands')->with('success', 'Brand updated successfully');
    }

    /**
     * Delete brand
     */
    public function destroyBrand(int $id)
    {
        $user = Auth::user();
        $brand = Brand::where('id', $id)
            ->where('create_user', $user->id)
            ->firstOrFail();

        Branch::where('brand_id', $brand->id)->delete();
        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }

    /**
     * Show offers page
     */
    public function offers()
    {
        $user = Auth::user();

        $offers = Offer::where('user_id', $user->id)
            ->with('brand')
            ->latest()
            ->paginate(15);

        return Inertia::render('Retailer/Offers', [
            'offers' => $offers,
        ]);
    }

    /**
     * Show create offer page
     */
    public function createOffer()
    {
        $user = Auth::user();
        $brands = Brand::where('create_user', $user->id)
            ->with('branches')
            ->get();

        return Inertia::render('Retailer/OfferCreate', [
            'brands' => $brands,
        ]);
    }

    /**
     * Store new offer
     */
    public function storeOffer(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'discount_type' => 'required|in:percentage,fixed,bogo',
            'discount_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_claims' => 'nullable|integer|min:1',
            'terms' => 'nullable|string',
            'branch_ids' => 'nullable|array',
            'all_branches' => 'boolean',
        ]);

        // Verify brand belongs to user
        if ($validated['brand_id']) {
            $brand = Brand::where('id', $validated['brand_id'])
                ->where('create_user', $user->id)
                ->firstOrFail();
        }

        $offer = Offer::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'max_claims' => $validated['max_claims'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'branch_ids' => $validated['branch_ids'] ?? null,
            'all_branches' => $validated['all_branches'] ?? false,
            'status' => 'active',
        ]);

        return redirect('/retailer/offers')->with('success', 'Offer created successfully');
    }

    /**
     * Show edit offer page
     */
    public function editOffer(int $id)
    {
        $user = Auth::user();
        $offer = Offer::where('id', $id)
            ->where('user_id', $user->id)
            ->with('brand')
            ->firstOrFail();

        $brands = Brand::where('create_user', $user->id)
            ->with('branches')
            ->get();

        return Inertia::render('Retailer/OfferEdit', [
            'offer' => $offer,
            'brands' => $brands,
        ]);
    }

    /**
     * Update offer
     */
    public function updateOffer(Request $request, int $id)
    {
        $user = Auth::user();
        $offer = Offer::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,id',
            'discount_type' => 'required|in:percentage,fixed,bogo',
            'discount_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_claims' => 'nullable|integer|min:1',
            'terms' => 'nullable|string',
            'branch_ids' => 'nullable|array',
            'all_branches' => 'boolean',
            'status' => 'nullable|in:draft,active,paused,expired',
        ]);

        $offer->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'max_claims' => $validated['max_claims'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'branch_ids' => $validated['branch_ids'] ?? null,
            'all_branches' => $validated['all_branches'] ?? false,
            'status' => $validated['status'] ?? $offer->status,
        ]);

        return redirect('/retailer/offers')->with('success', 'Offer updated successfully');
    }

    /**
     * Delete offer
     */
    public function destroyOffer(int $id)
    {
        $user = Auth::user();
        $offer = Offer::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $offer->delete();

        return redirect()->back()->with('success', 'Offer deleted successfully');
    }
}
