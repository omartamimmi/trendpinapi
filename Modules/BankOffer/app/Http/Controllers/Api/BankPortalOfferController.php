<?php

namespace Modules\BankOffer\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\BankOffer\app\Http\Requests\StoreBankOfferRequest;
use Modules\BankOffer\app\Http\Resources\BankOfferResource;
use Modules\BankOffer\app\Http\Resources\BankOfferBrandResource;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;

class BankPortalOfferController extends Controller
{
    public function __construct(
        protected BankOfferServiceInterface $offerService
    ) {}

    /**
     * List bank's own offers
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        if (!$bankId) {
            return response()->json(['message' => 'You must be associated with a bank'], 403);
        }

        $query = BankOffer::with(['cardType'])
            ->where('bank_id', $bankId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $offers = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

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
     * Create a new bank offer
     */
    public function store(StoreBankOfferRequest $request): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        if (!$bankId) {
            return response()->json(['message' => 'You must be associated with a bank'], 403);
        }

        $data = $request->validated();
        $data['bank_id'] = $bankId;
        $data['created_by'] = $user->id;
        $data['status'] = 'pending'; // Needs admin approval

        $offer = $this->offerService->createOffer($data);

        return response()->json([
            'message' => 'Offer created successfully. Pending admin approval.',
            'data' => new BankOfferResource($offer),
        ], 201);
    }

    /**
     * Show a specific offer
     */
    public function show(int $bankOffer): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        $offer = BankOffer::with(['cardType', 'participatingBrands.brand'])
            ->where('bank_id', $bankId)
            ->find($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json([
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * Update an offer (only if draft or pending)
     */
    public function update(StoreBankOfferRequest $request, int $bankOffer): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        $offer = BankOffer::where('bank_id', $bankId)
            ->whereIn('status', ['draft', 'pending'])
            ->find($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found or cannot be updated'], 404);
        }

        $data = $request->validated();
        unset($data['bank_id']); // Cannot change bank

        $offer = $this->offerService->updateOffer($bankOffer, $data);

        return response()->json([
            'message' => 'Offer updated successfully',
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * Delete an offer (only if draft)
     */
    public function destroy(int $bankOffer): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        $offer = BankOffer::where('bank_id', $bankId)
            ->where('status', 'draft')
            ->find($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found or cannot be deleted'], 404);
        }

        $this->offerService->deleteOffer($bankOffer);

        return response()->json([
            'message' => 'Offer deleted successfully',
        ]);
    }

    /**
     * View participation requests for an offer
     */
    public function participationRequests(int $bankOffer, Request $request): JsonResponse
    {
        $user = Auth::user();
        $bankId = $user->bank_id;

        $offer = BankOffer::where('bank_id', $bankId)->find($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        $query = BankOfferBrand::with(['brand'])
            ->where('bank_offer_id', $bankOffer);

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
}
