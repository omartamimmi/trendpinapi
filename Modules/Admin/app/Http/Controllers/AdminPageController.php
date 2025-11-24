<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Group;
use Modules\Business\app\Models\Branch;
use Modules\Business\app\Models\Business;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminPageController extends Controller
{
    /**
     * Show login page
     */
    public function loginPage(): Response
    {
        return Inertia::render('Admin/Login');
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
            $user = Auth::user();

            if (!$user->hasRole('admin')) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    /**
     * Show dashboard
     */
    public function dashboard(): Response
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
            'onboardings' => [
                'total' => RetailerOnboarding::count(),
                'in_progress' => RetailerOnboarding::where('status', 'in_progress')->count(),
                'completed' => RetailerOnboarding::where('status', 'completed')->count(),
            ],
            'subscriptions' => [
                'total' => RetailerSubscription::count(),
                'active' => RetailerSubscription::where('status', 'active')->count(),
                'pending' => RetailerSubscription::where('status', 'pending')->count(),
            ],
            'payments' => [
                'total_revenue' => SubscriptionPayment::where('status', 'completed')->sum('total'),
                'this_month_revenue' => SubscriptionPayment::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total'),
            ],
            'plans' => [
                'total' => SubscriptionPlan::count(),
                'active' => SubscriptionPlan::where('is_active', true)->count(),
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Show users page
     */
    public function users(): Response
    {
        $users = User::with('roles')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    /**
     * Store user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->back();
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()->back();
    }

    /**
     * Delete user
     */
    public function destroyUser(int $id)
    {
        User::findOrFail($id)->delete();
        return redirect()->back();
    }

    /**
     * Show roles page
     */
    public function roles(): Response
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store role
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back();
    }

    /**
     * Update role
     */
    public function updateRole(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->back();
    }

    /**
     * Delete role
     */
    public function destroyRole(int $id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->back();
    }

    /**
     * Show plans page
     */
    public function plans(Request $request): Response
    {
        $type = $request->get('type', 'retailer');
        $plans = SubscriptionPlan::where('type', $type)->get();

        return Inertia::render('Admin/Plans', [
            'plans' => $plans,
            'currentType' => $type,
        ]);
    }

    /**
     * Store plan
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:user,retailer,bank',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offers_count' => 'required|integer|min:1',
            'duration_months' => 'integer|min:1',
            'billing_period' => 'in:monthly,yearly',
            'trial_days' => 'integer|min:0',
            'color' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Set defaults
        $validated['duration_months'] = $validated['duration_months'] ?? 1;
        $validated['billing_period'] = $validated['billing_period'] ?? 'monthly';
        $validated['trial_days'] = $validated['trial_days'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        SubscriptionPlan::create($validated);

        return redirect()->back();
    }

    /**
     * Update plan
     */
    public function updatePlan(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:user,retailer,bank',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offers_count' => 'required|integer|min:1',
            'duration_months' => 'integer|min:1',
            'billing_period' => 'in:monthly,yearly',
            'trial_days' => 'integer|min:0',
            'color' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        SubscriptionPlan::findOrFail($id)->update($validated);

        return redirect()->back();
    }

    /**
     * Delete plan
     */
    public function destroyPlan(int $id)
    {
        SubscriptionPlan::findOrFail($id)->delete();
        return redirect()->back();
    }

    /**
     * Show payments page
     */
    public function payments(): Response
    {
        $payments = SubscriptionPayment::with(['user', 'subscription.plan'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Payments', [
            'payments' => $payments,
        ]);
    }

    /**
     * Show retailers page
     */
    public function retailers(Request $request): Response
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = User::role('retailer')->with(['retailerOnboarding']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $retailers = $query->paginate($perPage);

        return Inertia::render('Admin/Retailers', [
            'retailers' => $retailers->items(),
            'pagination' => [
                'total' => $retailers->total(),
                'per_page' => $retailers->perPage(),
                'current_page' => $retailers->currentPage(),
                'last_page' => $retailers->lastPage(),
                'from' => $retailers->firstItem(),
                'to' => $retailers->lastItem(),
                'prev_page_url' => $retailers->previousPageUrl(),
                'next_page_url' => $retailers->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Show single retailer profile
     */
    public function showRetailer(int $id): Response
    {
        $retailer = User::with(['retailerOnboarding', 'roles'])->findOrFail($id);
        $brands = Brand::where('create_user', $id)->with(['group', 'branches'])->get();
        $subscriptions = RetailerSubscription::where('user_id', $id)->with('plan')->get();

        return Inertia::render('Admin/RetailerProfile', [
            'retailer' => $retailer,
            'brands' => $brands,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show create retailer page
     */
    public function createRetailer(): Response
    {
        return Inertia::render('Admin/RetailerCreate');
    }

    /**
     * Store new retailer
     */
    public function storeRetailer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        $user->assignRole('retailer');

        return redirect('/admin/retailers')->with('success', 'Retailer created successfully');
    }

    /**
     * Update retailer
     */
    public function updateRetailer(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'phone' => 'nullable|string',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->back()->with('success', 'Retailer updated successfully');
    }

    /**
     * Delete retailer
     */
    public function destroyRetailer(int $id)
    {
        User::findOrFail($id)->delete();
        return redirect('/admin/retailers')->with('success', 'Retailer deleted successfully');
    }

    /**
     * Show retailer brands page
     */
    public function retailerBrands(int $retailerId): Response
    {
        $retailer = User::findOrFail($retailerId);
        $brands = Brand::where('create_user', $retailerId)
            ->with(['group', 'branches'])
            ->get();

        // Get groups that have brands from this retailer
        $groupIds = $brands->pluck('group_id')->filter()->unique()->values();
        $groups = Group::whereIn('id', $groupIds)->orWhereNull('business_id')->get();

        return Inertia::render('Admin/RetailerBrands', [
            'retailer' => $retailer,
            'brands' => $brands,
            'groups' => $groups,
        ]);
    }

    /**
     * Store brand for retailer
     */
    public function storeRetailerBrand(Request $request, int $retailerId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id',
            'gallery' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'location' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'group_id' => $validated['group_id'] ?? null,
            'gallery' => $validated['gallery'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'location' => $validated['location'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'create_user' => $retailerId,
            'status' => 'publish',
        ]);

        // Create branches if provided
        if ($request->has('branches')) {
            foreach ($request->branches as $branchData) {
                Branch::create([
                    'brand_id' => $brand->id,
                    'name' => $branchData['name'],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Brand created successfully');
    }

    /**
     * Show brand edit page
     */
    public function editBrand(int $id): Response
    {
        $brand = Brand::with(['group', 'branches'])->findOrFail($id);
        $retailer = User::findOrFail($brand->create_user);
        $groups = Group::all();

        return Inertia::render('Admin/BrandEdit', [
            'brand' => $brand,
            'retailer' => $retailer,
            'groups' => $groups,
        ]);
    }

    /**
     * Update brand
     */
    public function updateRetailerBrand(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'group_id' => 'nullable',
            'gallery' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'location' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'website_link' => 'nullable|string',
            'insta_link' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        // Handle empty group_id
        if (empty($validated['group_id'])) {
            $validated['group_id'] = null;
        }

        $brand = Brand::findOrFail($id);
        $brand->update($validated);

        // Update branches if provided
        if ($request->has('branches')) {
            // Remove existing branches
            Branch::where('brand_id', $brand->id)->delete();

            // Create new branches
            foreach ($request->branches as $branchData) {
                if (!empty($branchData['name'])) {
                    Branch::create([
                        'brand_id' => $brand->id,
                        'name' => $branchData['name'],
                    ]);
                }
            }
        }

        return redirect("/admin/retailers/{$brand->create_user}")->with('success', 'Brand updated successfully');
    }

    /**
     * Delete brand
     */
    public function destroyRetailerBrand(int $id)
    {
        $brand = Brand::findOrFail($id);
        Branch::where('brand_id', $brand->id)->delete();
        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }

    /**
     * Store group
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_id' => 'nullable|exists:businesses,id',
        ]);

        Group::create($validated);

        return redirect()->back()->with('success', 'Group created successfully');
    }

    /**
     * Update group
     */
    public function updateGroup(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Group::findOrFail($id)->update($validated);

        return redirect()->back()->with('success', 'Group updated successfully');
    }

    /**
     * Delete group
     */
    public function destroyGroup(int $id)
    {
        // Set brands' group_id to null before deleting group
        Brand::where('group_id', $id)->update(['group_id' => null]);
        Group::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Group deleted successfully');
    }

    /**
     * Show onboarding approvals page
     */
    public function onboardingApprovals(Request $request): Response
    {
        $status = $request->get('status', 'pending_approval');
        $search = $request->get('search');

        $query = RetailerOnboarding::with(['user'])
            ->where('status', 'completed');

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $onboardings = $query->latest()->get();

        // Get counts for each status
        $counts = [
            'pending_approval' => RetailerOnboarding::where('status', 'completed')
                ->where('approval_status', 'pending_approval')->count(),
            'approved' => RetailerOnboarding::where('status', 'completed')
                ->where('approval_status', 'approved')->count(),
            'changes_requested' => RetailerOnboarding::where('status', 'completed')
                ->where('approval_status', 'changes_requested')->count(),
            'rejected' => RetailerOnboarding::where('status', 'completed')
                ->where('approval_status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/OnboardingApprovals', [
            'onboardings' => $onboardings,
            'currentStatus' => $status,
            'counts' => $counts,
        ]);
    }

    /**
     * Show single onboarding review page
     */
    public function showOnboardingReview(int $id): Response
    {
        $onboarding = RetailerOnboarding::with(['user', 'approver'])->findOrFail($id);
        $user = $onboarding->user;
        $brands = Brand::where('create_user', $user->id)->with(['group', 'branches'])->get();
        $subscriptions = RetailerSubscription::where('user_id', $user->id)->with('plan')->get();

        return Inertia::render('Admin/OnboardingReview', [
            'onboarding' => $onboarding,
            'retailer' => $user,
            'brands' => $brands,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Approve onboarding
     */
    public function approveOnboarding(Request $request, int $id)
    {
        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'approved',
            'admin_notes' => $request->get('admin_notes'),
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Onboarding approved successfully');
    }

    /**
     * Request changes on onboarding
     */
    public function requestOnboardingChanges(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'changes_requested',
            'admin_notes' => $validated['admin_notes'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Changes requested successfully');
    }

    /**
     * Reject onboarding
     */
    public function rejectOnboarding(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $onboarding = RetailerOnboarding::findOrFail($id);

        $onboarding->update([
            'approval_status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Onboarding rejected');
    }
}
