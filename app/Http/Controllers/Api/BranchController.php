<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;

class BranchController extends Controller
{
    /**
     * Get all brands for authenticated user
     */
    public function getUserBrands(): JsonResponse
    {
        $user = Auth::user();

        // Get all brands created by this user
        $brands = Brand::where('create_user', $user->id)
            ->whereNull('deleted_at')
            ->withCount('branches')
            ->orderBy('name')
            ->get(['id', 'name', 'logo', 'location']);

        if ($brands->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No brands found for this user',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'logo' => $brand->logo,
                    'location' => $brand->location,
                    'branches_count' => $brand->branches_count,
                ];
            }),
        ]);
    }

    /**
     * Get branches for a specific brand
     */
    public function getBrandBranches($brandId): JsonResponse
    {
        $user = Auth::user();

        // Verify user owns this brand
        $brand = Brand::where('id', $brandId)
            ->where('create_user', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found or unauthorized',
            ], 404);
        }

        // Get all branches for this brand
        $branches = Branch::where('brand_id', $brand->id)
            ->whereNull('deleted_at')
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'phone', 'is_main']);

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Get branches for authenticated user's brand (legacy - single brand)
     */
    public function getUserBranches(): JsonResponse
    {
        $user = Auth::user();

        // Get user's brand (assuming user created the brand)
        $brand = Brand::where('create_user', $user->id)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'No brand found for this user',
                'data' => [],
            ], 404);
        }

        // Get all branches for this brand
        $branches = Branch::where('brand_id', $brand->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'phone', 'is_main']);

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ],
                'branches' => $branches,
            ],
        ]);
    }

    /**
     * Get single branch details
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $brand = Brand::where('create_user', $user->id)->first();
        
        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'No brand found for this user',
            ], 404);
        }

        $branch = Branch::where('brand_id', $brand->id)
            ->whereNull('deleted_at')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => $branch->location,
                'phone' => $branch->phone,
                'is_main' => $branch->is_main,
            ],
        ]);
    }
}
