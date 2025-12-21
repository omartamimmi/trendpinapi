<?php

namespace Modules\Business\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Business\app\DTO\BrandFilterDTO;
use Modules\Business\app\Http\Resources\BrandResource;
use Modules\Business\Services\Contracts\BrandApiServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class BrandController extends Controller
{
    public function __construct(
        private readonly BrandApiServiceInterface $brandApiService
    ) {}

    /**
     * Get all published brands with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $filters = BrandFilterDTO::fromRequest($request);
        $brands = $this->brandApiService->getPublishedBrands($filters);

        return $this->successResponse(
            BrandResource::collection($brands),
            $this->buildPaginationMeta($brands)
        );
    }

    /**
     * Get a single published brand with its branches
     *
     * Query params:
     * - lat: User latitude (for selecting nearest branch)
     * - lng: User longitude (for selecting nearest branch)
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $brand = $this->brandApiService->getPublishedBrandById($id);

        if (!$brand) {
            return $this->notFoundResponse('Brand not found');
        }

        $selectedBranch = $this->getSelectedBranch($brand, $request);

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
            'selected_branch_id' => $selectedBranch?->id,
        ]);
    }

    /**
     * Get a published brand by slug with its branches
     *
     * Query params:
     * - lat: User latitude (for selecting nearest branch)
     * - lng: User longitude (for selecting nearest branch)
     */
    public function showBySlug(string $slug, Request $request): JsonResponse
    {
        $brand = $this->brandApiService->getPublishedBrandBySlug($slug);

        if (!$brand) {
            return $this->notFoundResponse('Brand not found');
        }

        $selectedBranch = $this->getSelectedBranch($brand, $request);

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
            'selected_branch_id' => $selectedBranch?->id,
        ]);
    }

    /**
     * Get brand with a specific branch selected
     */
    public function showBranch(int $brandId, int $branchId): JsonResponse
    {
        $brand = $this->brandApiService->getPublishedBrandById($brandId);

        if (!$brand) {
            return $this->notFoundResponse('Brand not found');
        }

        $branch = $brand->branches->firstWhere('id', $branchId);

        if (!$branch) {
            return $this->notFoundResponse('Branch not found');
        }

        return response()->json([
            'success' => true,
            'data' => new BrandResource($brand),
            'selected_branch_id' => $branchId,
        ]);
    }

    /**
     * Get the nearest branch based on user location, or main branch, or first branch
     */
    private function getSelectedBranch($brand, Request $request)
    {
        $branches = $brand->branches;

        if ($branches->isEmpty()) {
            return null;
        }

        $lat = $request->query('lat');
        $lng = $request->query('lng');

        // If user location provided, find nearest branch
        if ($lat && $lng) {
            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($branches as $branch) {
                if ($branch->lat && $branch->lng) {
                    $distance = $this->calculateDistance(
                        (float) $lat,
                        (float) $lng,
                        (float) $branch->lat,
                        (float) $branch->lng
                    );

                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $nearest = $branch;
                    }
                }
            }

            if ($nearest) {
                return $nearest;
            }
        }

        // Fallback: main branch or first branch
        return $branches->firstWhere('is_main', true) ?? $branches->first();
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Build success response
     */
    private function successResponse(mixed $data, ?array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Build not found response
     */
    private function notFoundResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Build pagination meta data
     */
    private function buildPaginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
