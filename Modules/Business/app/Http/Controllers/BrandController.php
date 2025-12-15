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
     */
    public function show(int $id): JsonResponse
    {
        $brand = $this->brandApiService->getPublishedBrandById($id);

        if (!$brand) {
            return $this->notFoundResponse('Brand not found');
        }

        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Get a published brand by slug with its branches
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $brand = $this->brandApiService->getPublishedBrandBySlug($slug);

        if (!$brand) {
            return $this->notFoundResponse('Brand not found');
        }

        return $this->successResponse(new BrandResource($brand));
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
