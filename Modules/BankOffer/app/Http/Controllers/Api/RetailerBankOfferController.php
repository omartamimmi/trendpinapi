<?php

namespace Modules\BankOffer\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\BankOffer\app\Http\Requests\OfferParticipationRequest;
use Modules\BankOffer\app\Http\Resources\BankOfferResource;
use Modules\BankOffer\app\Http\Resources\BankOfferBrandResource;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;

class RetailerBankOfferController extends Controller
{
    public function __construct(
        protected BankOfferServiceInterface $offerService
    ) {}

    /**
     * List all available bank offers for retailer
     */
    public function index(Request $request): JsonResponse
    {
        $offers = $this->offerService->getAvailableOffersForRetailer();

        return response()->json([
            'data' => BankOfferResource::collection($offers),
        ]);
    }

    /**
     * Show a specific bank offer details
     */
    public function show(int $bankOffer): JsonResponse
    {
        $offer = $this->offerService->getOffer($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json([
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * Request participation in a bank offer
     */
    public function requestParticipation(OfferParticipationRequest $request, int $bankOffer): JsonResponse
    {
        $user = Auth::user();
        $brandId = $user->brand_id;

        if (!$brandId) {
            return response()->json(['message' => 'You must be associated with a brand'], 403);
        }

        try {
            $participation = $this->offerService->requestParticipation(
                $bankOffer,
                $brandId,
                $request->validated()
            );

            return response()->json([
                'message' => 'Participation request submitted successfully',
                'data' => new BankOfferBrandResource($participation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * List retailer's participation requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = Auth::user();
        $brandId = $user->brand_id;

        if (!$brandId) {
            return response()->json(['message' => 'You must be associated with a brand'], 403);
        }

        $query = BankOfferBrand::with(['bankOffer', 'bankOffer.bank'])
            ->where('brand_id', $brandId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => BankOfferBrandResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Cancel a participation request (only if pending)
     */
    public function cancelRequest(int $bankOfferBrand): JsonResponse
    {
        $user = Auth::user();
        $brandId = $user->brand_id;

        $request = BankOfferBrand::where('id', $bankOfferBrand)
            ->where('brand_id', $brandId)
            ->where('status', 'pending')
            ->first();

        if (!$request) {
            return response()->json(['message' => 'Request not found or cannot be cancelled'], 404);
        }

        $request->delete();

        return response()->json([
            'message' => 'Participation request cancelled',
        ]);
    }
}
