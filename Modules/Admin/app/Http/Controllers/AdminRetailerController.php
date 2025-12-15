<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
use Modules\RetailerOnboarding\app\Models\RetailerSubscription;

class AdminRetailerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = User::role('retailer')->with(['retailerOnboarding']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $retailers = $query->latest()->paginate(20);

        return Inertia::render('Admin/Retailers', [
            'retailers' => $retailers,
        ]);
    }

    public function show(int $id): Response
    {
        $retailer = User::with(['retailerOnboarding', 'roles'])->findOrFail($id);
        $brands = Brand::where('create_user', $id)->with(['branches'])->get();
        $subscriptions = RetailerSubscription::where('user_id', $id)->with('plan')->get();

        return Inertia::render('Admin/RetailerProfile', [
            'retailer' => $retailer,
            'brands' => $brands,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RetailerCreate');
    }

    public function store(Request $request)
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

    public function update(Request $request, int $id)
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

    public function destroy(int $id)
    {
        User::findOrFail($id)->delete();
        return redirect('/admin/retailers')->with('success', 'Retailer deleted successfully');
    }

    // Brand management methods
    public function brands(int $retailerId): Response
    {
        $retailer = User::findOrFail($retailerId);
        $brands = Brand::where('create_user', $retailerId)
            ->with(['branches', 'media'])
            ->get()
            ->map(function ($brand) {
                $logoMedia = $brand->getFirstMedia('logo');
                $galleryMedia = $brand->getMedia('gallery');

                return [
                    ...$brand->toArray(),
                    'logo_media' => $logoMedia ? [
                        'id' => $logoMedia->id,
                        'file_name' => $logoMedia->file_name,
                        'file_type' => $logoMedia->file_type,
                        'url' => $logoMedia->url,
                        'thumbnail_url' => $logoMedia->thumbnail_url,
                    ] : null,
                    'gallery_media' => $galleryMedia->map(fn($m) => [
                        'id' => $m->id,
                        'file_name' => $m->file_name,
                        'file_type' => $m->file_type,
                        'url' => $m->url,
                        'thumbnail_url' => $m->thumbnail_url,
                    ])->toArray(),
                ];
            });

        return Inertia::render('Admin/RetailerBrands', [
            'retailer' => $retailer,
            'brands' => $brands,
        ]);
    }

    public function storeBrand(Request $request, int $retailerId)
    {
        $request->validate([
            'brands' => 'required|array',
            'brands.*.name' => 'required|string|max:255',
            'brands.*.title' => 'nullable|string|max:255',
            'brands.*.title_ar' => 'nullable|string|max:255',
            'brands.*.description' => 'nullable|string',
            'brands.*.description_ar' => 'nullable|string',
            'brands.*.phone_number' => 'nullable|string|max:50',
            'brands.*.website_link' => 'nullable|url|max:255',
            'brands.*.insta_link' => 'nullable|url|max:255',
            'brands.*.facebook_link' => 'nullable|url|max:255',
            'brands.*.status' => 'nullable|in:draft,publish',
            'brands.*.logo_id' => 'nullable|integer',
            'brands.*.gallery_ids' => 'nullable|array',
            'brands.*.gallery_ids.*' => 'integer',
            'brands.*.branches' => 'nullable|array',
            'brands.*.branches.*.name' => 'nullable|string|max:255',
            'brands.*.branches.*.location' => 'nullable|string',
            'brands.*.branches.*.lat' => 'nullable|numeric',
            'brands.*.branches.*.lng' => 'nullable|numeric',
            'brands.*.branches.*.status' => 'nullable|in:draft,publish',
        ]);

        $brandsData = $request->input('brands', []);

        // Validate: cannot publish without at least one published branch
        foreach ($brandsData as $index => $brandData) {
            $branches = $brandData['branches'] ?? [];
            $hasPublishedBranch = !empty($branches) && count(array_filter($branches, fn($b) =>
                (!empty($b['name']) || !empty($b['location'])) && ($b['status'] ?? 'draft') === 'publish'
            )) > 0;

            if (($brandData['status'] ?? 'draft') === 'publish' && !$hasPublishedBranch) {
                $brandName = $brandData['name'] ?? 'Brand ' . ($index + 1);
                return redirect()->back()
                    ->withErrors(['brands' => "'{$brandName}' must have at least one published branch to be published."])
                    ->withInput();
            }
        }

        foreach ($brandsData as $brandData) {
            $brandFields = [
                'name' => $brandData['name'],
                'title' => $brandData['title'] ?? null,
                'title_ar' => $brandData['title_ar'] ?? null,
                'description' => $brandData['description'] ?? null,
                'description_ar' => $brandData['description_ar'] ?? null,
                'phone_number' => $brandData['phone_number'] ?? null,
                'website_link' => $brandData['website_link'] ?? null,
                'insta_link' => $brandData['insta_link'] ?? null,
                'facebook_link' => $brandData['facebook_link'] ?? null,
                'status' => $brandData['status'] ?? 'draft',
            ];

            // Check if updating existing brand or creating new
            if (!empty($brandData['id'])) {
                $brand = Brand::find($brandData['id']);
                if ($brand && $brand->create_user == $retailerId) {
                    $brand->update($brandFields);
                    // Delete existing branches and recreate
                    Branch::where('brand_id', $brand->id)->delete();
                }
            } else {
                // Create new brand
                $brand = Brand::create(array_merge($brandFields, [
                    'create_user' => $retailerId,
                ]));
            }

            if (isset($brand)) {
                // Handle logo media
                if (!empty($brandData['logo_id'])) {
                    $brand->syncMedia([$brandData['logo_id']], 'logo');
                } else {
                    $brand->clearMediaCollection('logo');
                }

                // Handle gallery media
                if (!empty($brandData['gallery_ids'])) {
                    $brand->syncMedia($brandData['gallery_ids'], 'gallery');
                } else {
                    $brand->clearMediaCollection('gallery');
                }

                // Create branches with location data
                if (!empty($brandData['branches'])) {
                    foreach ($brandData['branches'] as $branchData) {
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
            }
        }

        return redirect()->back()->with('success', 'Brands saved successfully');
    }

    public function editBrand(int $id): Response
    {
        $brand = Brand::with(['branches', 'media', 'categories'])->findOrFail($id);
        $retailer = User::findOrFail($brand->create_user);
        $categories = \Modules\Category\Models\Category::where('status', 'published')->get();

        $logoMedia = $brand->getFirstMedia('logo');
        $galleryMedia = $brand->getMedia('gallery');

        $brandData = [
            ...$brand->toArray(),
            'logo_media' => $logoMedia ? [
                'id' => $logoMedia->id,
                'file_name' => $logoMedia->file_name,
                'file_type' => $logoMedia->file_type,
                'url' => $logoMedia->url,
                'thumbnail_url' => $logoMedia->thumbnail_url,
            ] : null,
            'gallery_media' => $galleryMedia->map(fn($m) => [
                'id' => $m->id,
                'file_name' => $m->file_name,
                'file_type' => $m->file_type,
                'url' => $m->url,
                'thumbnail_url' => $m->thumbnail_url,
            ])->toArray(),
        ];

        return Inertia::render('Admin/BrandEdit', [
            'brand' => $brandData,
            'retailer' => $retailer,
            'categories' => $categories,
        ]);
    }

    public function updateBrand(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'website_link' => 'nullable|string|max:255',
            'insta_link' => 'nullable|string|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,publish',
            'logo_id' => 'nullable|integer',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'integer',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $brand = Brand::findOrFail($id);

        // Validate: cannot publish without at least one published branch
        $branches = $request->input('branches', []);
        $hasPublishedBranch = !empty($branches) && count(array_filter($branches, fn($b) =>
            (!empty($b['name']) || !empty($b['location'])) && ($b['status'] ?? 'draft') === 'publish'
        )) > 0;

        if ($request->input('status') === 'publish' && !$hasPublishedBranch) {
            return redirect()->back()
                ->withErrors(['status' => 'Brand must have at least one published branch to be published.'])
                ->withInput();
        }

        // Remove category_ids from validated array before update
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $brand->update($validated);

        // Sync categories
        $brand->categories()->sync($categoryIds);

        // Handle logo media
        if ($request->has('logo_id')) {
            if (!empty($request->logo_id)) {
                $brand->syncMedia([$request->logo_id], 'logo');
            } else {
                $brand->clearMediaCollection('logo');
            }
        }

        // Handle gallery media
        if ($request->has('gallery_ids')) {
            if (!empty($request->gallery_ids)) {
                $brand->syncMedia($request->gallery_ids, 'gallery');
            } else {
                $brand->clearMediaCollection('gallery');
            }
        }

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

        return redirect("/admin/retailers/{$brand->create_user}")->with('success', 'Brand updated successfully');
    }

    public function destroyBrand(int $id)
    {
        $brand = Brand::findOrFail($id);
        Branch::where('brand_id', $brand->id)->delete();
        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully');
    }
}
