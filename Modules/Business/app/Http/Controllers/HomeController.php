<?php

namespace Modules\Business\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Business\app\Models\Brand;
use Modules\Business\app\Models\Branch;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\Category\Models\Category;
use Modules\Media\Helpers\FileHelper;

class HomeController extends Controller
{
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Get home page data
     *
     * Query params:
     * - lat: User latitude (for distance calculation)
     * - lng: User longitude
     * - sort_by: How to sort brands (best_offer, ending_soon, most_popular, nearest, bank, default)
     * - category_ids: Filter by categories (comma-separated, e.g., "1,2,3")
     * - bank_id: Filter by bank offers
     * - search: Search brands by name/title
     * - per_page: Number of brands per page (default 10)
     * - page: Page number (default 1)
     */
    public function index(Request $request): JsonResponse
    {
        // Authenticate user from Bearer token if present (for wishlist status)
        $this->authenticateFromToken($request);

        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $sortBy = $request->query('sort_by', 'default');
        $categoryIds = $this->parseIds($request->query('category_ids'));
        $bankId = $request->query('bank_id');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);

        $brandsData = $this->getBrands($lat, $lng, $sortBy, $categoryIds, $bankId, $search, $perPage, $page);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $this->getCategories(),
                'banks' => $this->getBanksWithOffers(),
                'brands' => $brandsData['items'],
                'pagination' => $brandsData['pagination'],
            ],
        ]);
    }

    /**
     * Get active categories
     */
    private function getCategories(): array
    {
        return Category::where('status', 'publish')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'name_ar' => $cat->name_ar,
                'image' => $cat->featured_image,
            ])
            ->toArray();
    }

    /**
     * Get banks with active offers
     */
    private function getBanksWithOffers(): array
    {
        return Bank::where('status', 'active')
            ->withCount(['offers' => function ($q) {
                $q->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            }])
            ->having('offers_count', '>', 0)
            ->with('logo')
            ->orderBy('name')
            ->get()
            ->map(fn($bank) => [
                'id' => $bank->id,
                'name' => $bank->name,
                'name_ar' => $bank->name_ar,
                'logo' => $bank->logo?->url,
                'offers_count' => $bank->offers_count,
            ])
            ->toArray();
    }

    /**
     * Parse comma-separated IDs into array
     */
    private function parseIds(?string $ids): array
    {
        if (!$ids) {
            return [];
        }

        return array_filter(array_map('intval', explode(',', $ids)));
    }

    /**
     * Get brands with filtering and sorting
     */
    private function getBrands(?string $lat, ?string $lng, string $sortBy, array $categoryIds, ?string $bankId, ?string $search, int $perPage, int $page): array
    {
        $query = Brand::where('status', 'publish')
            ->with([
                'branches' => fn($q) => $q->where('status', 'publish'),
                'categories',
                'activeOffers',
                'activeBankOfferBrands.bankOffer.bank'
            ]);

        // Search by name/title
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        // Filter by categories (multiple)
        if (!empty($categoryIds)) {
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        // Filter by bank (brands that have offers from this bank)
        if ($bankId) {
            $query->whereHas('activeBankOfferBrands.bankOffer', fn($q) => $q->where('bank_id', $bankId));
        }

        // Filter to only brands with bank offers when sort_by=bank
        if ($sortBy === 'bank') {
            $query->whereHas('activeBankOfferBrands');
        }

        $brands = $query->get();

        // Calculate distances and best offers for each brand
        $brands = $brands->map(function ($brand) use ($lat, $lng, $sortBy) {
            $nearestBranch = $this->getNearestBranch($brand->branches, $lat, $lng);

            // Get best offer based on sort criteria
            $bestByCriteria = match ($sortBy) {
                'ending_soon' => 'ending_soon',
                'most_popular' => 'most_popular',
                default => 'highest_value',
            };

            $bestOffer = $brand->getBestOffer($bestByCriteria);
            $bestBankOffer = $brand->getBestBankOffer($bestByCriteria);

            $brand->_nearest_branch = $nearestBranch;
            $brand->_distance = $nearestBranch['distance'] ?? PHP_FLOAT_MAX;
            $brand->_best_offer = $bestOffer;
            $brand->_best_bank_offer = $bestBankOffer;
            $brand->_best_offer_value = $bestOffer?->discount_value ?? 0;
            $brand->_best_offer_end_date = $bestOffer?->end_date ?? now()->addYears(10);
            $brand->_best_offer_claims = $bestOffer?->claims_count ?? 0;
            $brand->_best_bank_offer_value = $bestBankOffer?->offer_value ?? 0;

            return $brand;
        });

        // Sort based on criteria
        $brands = match ($sortBy) {
            'best_offer' => $brands->sortByDesc('_best_offer_value'),
            'ending_soon' => $brands->sortBy('_best_offer_end_date'),
            'most_popular' => $brands->sortByDesc('_best_offer_claims'),
            'nearest' => $brands->sortBy('_distance'),
            'bank' => $brands->sortByDesc('_best_bank_offer_value'),
            default => $brands->sortByDesc('featured')->sortByDesc('_best_offer_value'),
        };

        // Pagination
        $total = $brands->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // Paginate and format
        $items = $brands->slice($offset, $perPage)->values()->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'title' => $brand->title,
                'title_ar' => $brand->title_ar,
                'slug' => $brand->slug,
                'logo' => $brand->logo_url,
                'featured_image' => $this->getBrandFeaturedImage($brand),
                'gallery' => $brand->gallery_images ?: $brand->getGallery(true) ?: [],
                'is_wishlisted' => $brand->isWishList() === '-solid',
                'categories' => $brand->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'name_ar' => $cat->name_ar ?? null,
                ]),
                'nearest_branch' => $brand->_nearest_branch ? [
                    'id' => $brand->_nearest_branch['branch']->id,
                    'name' => $brand->_nearest_branch['branch']->name,
                    'location' => $brand->_nearest_branch['branch']->location,
                    'distance' => $brand->_nearest_branch['distance'] ? round($brand->_nearest_branch['distance'], 2) : null,
                ] : null,
                'best_offer' => $brand->_best_offer ? [
                    'id' => $brand->_best_offer->id,
                    'name' => $brand->_best_offer->name,
                    'label' => $this->getOfferLabel($brand->_best_offer->discount_type, $brand->_best_offer->discount_value),
                    'end_date' => $brand->_best_offer->end_date?->toDateString(),
                ] : null,
                'best_bank_offer' => $brand->_best_bank_offer ? [
                    'id' => $brand->_best_bank_offer->id,
                    'title' => $brand->_best_bank_offer->title,
                    'label' => $this->getOfferLabel($brand->_best_bank_offer->offer_type, $brand->_best_bank_offer->offer_value),
                    'end_date' => $brand->_best_bank_offer->end_date?->toDateString(),
                    'bank' => $brand->_best_bank_offer->bank ? [
                        'id' => $brand->_best_bank_offer->bank->id,
                        'name' => $brand->_best_bank_offer->bank->name,
                        'logo' => $brand->_best_bank_offer->bank->logo?->url,
                    ] : null,
                ] : null,
            ];
        })->toArray();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'next_page' => $page < $lastPage ? $page + 1 : null,
                'prev_page' => $page > 1 ? $page - 1 : null,
            ],
        ];
    }

    /**
     * Get nearest branch from collection
     */
    private function getNearestBranch($branches, ?string $lat, ?string $lng): ?array
    {
        if ($branches->isEmpty()) {
            return null;
        }

        // If no location, return main branch or first
        if (!$lat || !$lng) {
            $branch = $branches->firstWhere('is_main', true) ?? $branches->first();
            return ['branch' => $branch, 'distance' => null];
        }

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
            return ['branch' => $nearest, 'distance' => $minDistance];
        }

        // Fallback to main or first
        $branch = $branches->firstWhere('is_main', true) ?? $branches->first();
        return ['branch' => $branch, 'distance' => null];
    }

    /**
     * Calculate distance using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Generate offer label
     */
    private function getOfferLabel(string $type, $value): string
    {
        return match ($type) {
            'percentage' => "{$value}% Off",
            'fixed' => "JOD {$value} Off",
            'cashback' => "{$value}% Cashback",
            'bogo' => 'Buy 1 Get 1',
            default => "{$value}% Off",
        };
    }

    /**
     * Get brand featured image URL
     * Returns null instead of false if image not found
     */
    private function getBrandFeaturedImage(Brand $brand): ?string
    {
        // Try image_id first
        if ($brand->image_id) {
            $url = FileHelper::url($brand->image_id, 'full');
            if ($url && $url !== false) {
                return $url;
            }
        }

        // Try featured_mobile as fallback
        if (!empty($brand->featured_mobile)) {
            return $brand->featured_mobile;
        }

        // Try to get from gallery
        $gallery = $brand->gallery_images ?: $brand->getGallery(true);
        if (!empty($gallery) && isset($gallery[0]['large'])) {
            return $gallery[0]['large'];
        }

        return null;
    }

    /**
     * Authenticate user from Bearer token if present
     * This allows public routes to access user context for features like wishlist status
     */
    private function authenticateFromToken(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        $token = $request->bearerToken();
        if (!$token) {
            return;
        }

        // Try to find the token and authenticate the user
        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken) {
            // Load the tokenable (user) relationship
            $user = $accessToken->tokenable;
            if ($user) {
                // Check if token is not expired
                if (!$accessToken->expires_at || $accessToken->expires_at->isFuture()) {
                    Auth::setUser($user);
                }
            }
        }
    }
}
