<?php

namespace Modules\BankOffer\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BankOffer\app\Http\Resources\BankOfferResource;
use Modules\BankOffer\app\Http\Resources\BankResource;
use Modules\BankOffer\app\Http\Resources\CardTypeResource;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;
use Modules\BankOffer\app\Models\CardType;
use Modules\BankOffer\Services\Contracts\BankOfferServiceInterface;

class MobileBankOfferController extends Controller
{
    public function __construct(
        protected BankOfferServiceInterface $offerService
    ) {}

    /**
     * List all active bank offers
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankOffer::with(['bank', 'cardType'])
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());

        // Filter by bank
        if ($request->has('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        // Filter by card type
        if ($request->has('card_type_id')) {
            $query->where('card_type_id', $request->card_type_id);
        }

        // Filter by offer type
        if ($request->has('offer_type')) {
            $query->where('offer_type', $request->offer_type);
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
     * Show a specific bank offer
     */
    public function show(int $bankOffer): JsonResponse
    {
        $offer = BankOffer::with(['bank', 'cardType', 'participatingBrands.brand'])
            ->where('status', 'active')
            ->find($bankOffer);

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        return response()->json([
            'data' => new BankOfferResource($offer),
        ]);
    }

    /**
     * List all banks
     *
     * Query params:
     * - with_offers_only: boolean - only return banks with active offers
     */
    public function banks(Request $request): JsonResponse
    {
        $query = Bank::with('logo')
            ->where('status', 'active')
            ->withCount(['offers' => function ($q) {
                $q->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            }])
            ->withCount('cardTypes');

        // Optional: filter to only banks with offers
        if ($request->boolean('with_offers_only')) {
            $query->having('offers_count', '>', 0);
        }

        $banks = $query->orderBy('name')->get();

        return response()->json([
            'data' => BankResource::collection($banks),
        ]);
    }

    /**
     * Get offers by bank
     */
    public function offersByBank(int $bank, Request $request): JsonResponse
    {
        $bankModel = Bank::where('status', 'active')->find($bank);

        if (!$bankModel) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        $offers = BankOffer::with(['cardType'])
            ->where('bank_id', $bank)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'bank' => new BankResource($bankModel),
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
     * Get offers for a specific brand (combined with brand offers)
     */
    public function brandOffers(int $brand, Request $request): JsonResponse
    {
        $result = $this->offerService->getOffersWithBrandProviders($brand);

        // Filter by type if requested
        $type = $request->get('type', 'all');
        $bankId = $request->get('bank_id');

        $bankOffers = $result['bank_offers'];

        if ($bankId) {
            $bankOffers = $bankOffers->filter(function ($offer) use ($bankId) {
                return $offer->bank_id == $bankId;
            });
        }

        return response()->json([
            'offer_providers' => $result['offer_providers'],
            'bank_offers' => BankOfferResource::collection($bankOffers),
            'settings' => $result['settings'],
        ]);
    }

    /**
     * Lookup card type and bank by BIN (first 6 digits)
     *
     * POST /api/v1/card-lookup
     * Body: { "bin": "411111" } or { "bin": "4111111111111111" }
     *
     * Returns card type, bank info, and detected network
     */
    public function cardLookup(Request $request): JsonResponse
    {
        $request->validate([
            'bin' => 'required|string|min:6',
        ]);

        $bin = preg_replace('/\D/', '', $request->input('bin'));

        if (strlen($bin) < 6) {
            return response()->json([
                'message' => 'BIN must be at least 6 digits',
            ], 422);
        }

        // Detect network from BIN
        $detectedNetwork = CardType::detectNetworkFromBin($bin);

        // Try to find matching card type
        $cardType = CardType::findByBin($bin);

        if ($cardType) {
            return response()->json([
                'found' => true,
                'card_type' => new CardTypeResource($cardType),
                'bank' => $cardType->bank ? new BankResource($cardType->bank) : null,
                'detected_network' => $detectedNetwork,
            ]);
        }

        // No specific card type found, return just the detected network
        return response()->json([
            'found' => false,
            'card_type' => null,
            'bank' => null,
            'detected_network' => $detectedNetwork,
            'message' => 'Card type not found in our database',
        ]);
    }
}
