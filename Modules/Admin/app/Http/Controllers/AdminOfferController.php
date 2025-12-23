<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Business\app\Models\Brand;
use Modules\RetailerOnboarding\app\Models\Offer;

class AdminOfferController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $retailerId = $request->get('retailer_id');

        $query = Offer::with(['user', 'brand']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('brand', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($retailerId) {
            $query->where('user_id', $retailerId);
        }

        $offers = $query->latest()->paginate(20)->withQueryString();

        // Get retailers for filter dropdown
        $retailers = User::role('retailer')->select('id', 'name', 'email')->get();

        // Calculate stats
        $totalOffers = Offer::count();
        $activeOffers = Offer::where('status', 'active')->count();
        $totalClaims = Offer::sum('claims_count');
        $totalViews = Offer::sum('views_count');

        return Inertia::render('Admin/Offers', [
            'offers' => $offers,
            'retailers' => $retailers,
            'stats' => [
                'total' => $totalOffers,
                'active' => $activeOffers,
                'claims' => $totalClaims,
                'views' => $totalViews,
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'retailer_id' => $retailerId,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $retailerId = $request->get('retailer_id');

        // Get all retailers
        $retailers = User::role('retailer')->select('id', 'name', 'email')->get();

        // Get brands - if retailer_id is provided, get only that retailer's brands
        $brands = collect();
        if ($retailerId) {
            $brands = Brand::where('create_user', $retailerId)
                ->with('branches')
                ->get();
        }

        return Inertia::render('Admin/OfferCreate', [
            'retailers' => $retailers,
            'brands' => $brands,
            'selectedRetailerId' => $retailerId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        // Verify brand belongs to the selected user
        if ($validated['brand_id']) {
            $brand = Brand::find($validated['brand_id']);
            if ($brand && $brand->create_user != $validated['user_id']) {
                return redirect()->back()
                    ->withErrors(['brand_id' => 'Brand does not belong to the selected retailer.'])
                    ->withInput();
            }
        }

        Offer::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'max_claims' => $validated['max_claims'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'branch_ids' => $validated['branch_ids'] ?? [],
            'all_branches' => $validated['all_branches'] ?? false,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect('/admin/offers')->with('success', 'Offer created successfully');
    }

    public function edit(int $id): Response
    {
        $offer = Offer::with(['user', 'brand'])->findOrFail($id);

        // Get all retailers
        $retailers = User::role('retailer')->select('id', 'name', 'email')->get();

        // Get brands for the offer's retailer
        $brands = Brand::where('create_user', $offer->user_id)
            ->with('branches')
            ->get();

        return Inertia::render('Admin/OfferEdit', [
            'offer' => $offer,
            'retailers' => $retailers,
            'brands' => $brands,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $offer = Offer::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        // Verify brand belongs to the selected user
        if ($validated['brand_id']) {
            $brand = Brand::find($validated['brand_id']);
            if ($brand && $brand->create_user != $validated['user_id']) {
                return redirect()->back()
                    ->withErrors(['brand_id' => 'Brand does not belong to the selected retailer.'])
                    ->withInput();
            }
        }

        $offer->update([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'max_claims' => $validated['max_claims'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'branch_ids' => $validated['branch_ids'] ?? [],
            'all_branches' => $validated['all_branches'] ?? false,
            'status' => $validated['status'] ?? $offer->status,
        ]);

        return redirect('/admin/offers')->with('success', 'Offer updated successfully');
    }

    public function destroy(int $id)
    {
        Offer::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Offer deleted successfully');
    }

    // API endpoint to get brands for a retailer (used by frontend)
    public function getBrands(int $retailerId)
    {
        $brands = Brand::where('create_user', $retailerId)
            ->with('branches')
            ->get();

        return response()->json($brands);
    }
}
