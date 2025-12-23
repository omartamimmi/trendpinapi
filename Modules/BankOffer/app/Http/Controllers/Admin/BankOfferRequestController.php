<?php

namespace Modules\BankOffer\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\BankOffer\app\Http\Resources\BankOfferBrandResource;
use Modules\BankOffer\app\Models\BankOfferBrand;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;

class BankOfferRequestController extends Controller
{
    public function __construct(
        protected BankOfferServiceInterface $offerService
    ) {}

    /**
     * List all participation requests
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankOfferBrand::with(['bankOffer', 'bankOffer.bank', 'brand']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('bank_offer_id')) {
            $query->where('bank_offer_id', $request->bank_offer_id);
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
     * Show a specific request
     */
    public function show(int $bankOfferBrand): JsonResponse
    {
        $request = BankOfferBrand::with(['bankOffer', 'bankOffer.bank', 'brand', 'approver'])
            ->find($bankOfferBrand);

        if (!$request) {
            return response()->json(['message' => 'Request not found'], 404);
        }

        return response()->json([
            'data' => new BankOfferBrandResource($request),
        ]);
    }

    /**
     * Approve a participation request
     */
    public function approve(int $bankOfferBrand): JsonResponse
    {
        $request = $this->offerService->approveParticipation($bankOfferBrand, Auth::id());

        return response()->json([
            'message' => 'Participation approved',
            'data' => new BankOfferBrandResource($request),
        ]);
    }

    /**
     * Reject a participation request
     */
    public function reject(int $bankOfferBrand): JsonResponse
    {
        $request = $this->offerService->rejectParticipation($bankOfferBrand);

        return response()->json([
            'message' => 'Participation rejected',
            'data' => new BankOfferBrandResource($request),
        ]);
    }
}
