<?php

namespace Modules\BankOffer\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BankOffer\app\Http\Requests\StoreCardTypeRequest;
use Modules\BankOffer\app\Http\Requests\UpdateCardTypeRequest;
use Modules\BankOffer\app\Http\Resources\CardTypeResource;
use Modules\BankOffer\Repositories\Contracts\CardTypeRepositoryInterface;

class CardTypeController extends Controller
{
    public function __construct(
        protected CardTypeRepositoryInterface $cardTypeRepository
    ) {}

    /**
     * List all card types
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'bank_id', 'card_network', 'status']);
        $perPage = $request->get('per_page', 15);

        $cardTypes = $this->cardTypeRepository->paginate($perPage, $filters);

        return response()->json([
            'data' => CardTypeResource::collection($cardTypes),
            'meta' => [
                'current_page' => $cardTypes->currentPage(),
                'last_page' => $cardTypes->lastPage(),
                'per_page' => $cardTypes->perPage(),
                'total' => $cardTypes->total(),
            ],
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return response()->json(['message' => 'Create card type form']);
    }

    /**
     * Store a new card type
     */
    public function store(StoreCardTypeRequest $request): JsonResponse
    {
        $cardType = $this->cardTypeRepository->create($request->validated());

        return response()->json([
            'message' => 'Card type created successfully',
            'data' => new CardTypeResource($cardType),
        ], 201);
    }

    /**
     * Show a specific card type
     */
    public function show(int $cardType): JsonResponse
    {
        $cardTypeModel = $this->cardTypeRepository->find($cardType);

        if (!$cardTypeModel) {
            return response()->json(['message' => 'Card type not found'], 404);
        }

        return response()->json([
            'data' => new CardTypeResource($cardTypeModel),
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(int $cardType)
    {
        $cardTypeModel = $this->cardTypeRepository->find($cardType);

        return response()->json([
            'data' => new CardTypeResource($cardTypeModel),
        ]);
    }

    /**
     * Update a card type
     */
    public function update(UpdateCardTypeRequest $request, int $cardType): JsonResponse
    {
        $cardTypeModel = $this->cardTypeRepository->update($cardType, $request->validated());

        return response()->json([
            'message' => 'Card type updated successfully',
            'data' => new CardTypeResource($cardTypeModel),
        ]);
    }

    /**
     * Delete a card type
     */
    public function destroy(int $cardType): JsonResponse
    {
        $this->cardTypeRepository->delete($cardType);

        return response()->json([
            'message' => 'Card type deleted successfully',
        ]);
    }
}
