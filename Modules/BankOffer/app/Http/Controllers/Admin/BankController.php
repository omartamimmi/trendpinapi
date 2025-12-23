<?php

namespace Modules\BankOffer\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BankOffer\app\Http\Requests\StoreBankRequest;
use Modules\BankOffer\app\Http\Requests\UpdateBankRequest;
use Modules\BankOffer\app\Http\Resources\BankResource;
use Modules\BankOffer\Repositories\Contracts\BankRepositoryInterface;

class BankController extends Controller
{
    public function __construct(
        protected BankRepositoryInterface $bankRepository
    ) {}

    /**
     * List all banks
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->get('per_page', 15);

        $banks = $this->bankRepository->paginate($perPage, $filters);

        return response()->json([
            'data' => BankResource::collection($banks),
            'meta' => [
                'current_page' => $banks->currentPage(),
                'last_page' => $banks->lastPage(),
                'per_page' => $banks->perPage(),
                'total' => $banks->total(),
            ],
        ]);
    }

    /**
     * Show create form (for web views)
     */
    public function create()
    {
        return response()->json(['message' => 'Create bank form']);
    }

    /**
     * Store a new bank
     */
    public function store(StoreBankRequest $request): JsonResponse
    {
        $bank = $this->bankRepository->create($request->validated());

        return response()->json([
            'message' => 'Bank created successfully',
            'data' => new BankResource($bank),
        ], 201);
    }

    /**
     * Show a specific bank
     */
    public function show(int $bank): JsonResponse
    {
        $bankModel = $this->bankRepository->find($bank);

        if (!$bankModel) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        return response()->json([
            'data' => new BankResource($bankModel),
        ]);
    }

    /**
     * Show edit form (for web views)
     */
    public function edit(int $bank)
    {
        $bankModel = $this->bankRepository->find($bank);

        return response()->json([
            'data' => new BankResource($bankModel),
        ]);
    }

    /**
     * Update a bank
     */
    public function update(UpdateBankRequest $request, int $bank): JsonResponse
    {
        $bankModel = $this->bankRepository->update($bank, $request->validated());

        return response()->json([
            'message' => 'Bank updated successfully',
            'data' => new BankResource($bankModel),
        ]);
    }

    /**
     * Delete a bank
     */
    public function destroy(int $bank): JsonResponse
    {
        $this->bankRepository->delete($bank);

        return response()->json([
            'message' => 'Bank deleted successfully',
        ]);
    }
}
