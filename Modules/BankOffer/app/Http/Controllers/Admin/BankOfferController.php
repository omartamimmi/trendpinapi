<?php

namespace Modules\BankOffer\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\BankOffer\app\Http\Resources\BankOfferResource;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;

class BankOfferController extends Controller
{
    public function __construct(
        protected BankOfferServiceInterface $offerService
    ) {}

    /**
     * List all bank offers
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'bank_id', 'status', 'offer_type']);
        $perPage = $request->get('per_page', 15);

        $offers = $this->offerService->getOffers($filters, $perPage);

        return response()->json([
            'data' => BankOfferResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total(),
            ],
        ]);
    }

    /**
     * Show a specific offer
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
     * Approve an offer
     */
    public function approve(int $bankOffer): JsonResponse
    {
        $offer = $this->offerService->approveOffer($bankOffer, Auth::id());

        return response()->json([
            'message' => 'Offer approved successfully',
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * Reject an offer
     */
    public function reject(int $bankOffer): JsonResponse
    {
        $offer = $this->offerService->rejectOffer($bankOffer);

        return response()->json([
            'message' => 'Offer rejected',
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * Update offer status
     */
    public function updateStatus(Request $request, int $bankOffer): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,pending,active,paused,expired',
        ]);

        $offer = $this->offerService->updateOfferStatus($bankOffer, $request->status);

        return response()->json([
            'message' => 'Offer status updated',
            'data' => new BankOfferResource($offer),
        ]);
    }
}
