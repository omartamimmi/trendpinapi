<?php

namespace Modules\BankOffer\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\BankOffer\app\Http\Resources\BankResource;
use Modules\BankOffer\app\Http\Resources\BankOfferResource;
use Modules\BankOffer\app\Models\Bank;
use Modules\BankOffer\app\Models\BankOffer;

class UserBankController extends Controller
{
    /**
     * Get user's selected banks
     */
    public function index(): JsonResponse
    {
        $bankIds = DB::table('user_banks')
            ->where('user_id', Auth::id())
            ->pluck('bank_id');

        $banks = Bank::with('logo')
            ->whereIn('id', $bankIds)
            ->where('status', 'active')
            ->get();

        return response()->json([
            'data' => BankResource::collection($banks),
        ]);
    }

    /**
     * Update user's selected banks (replace all)
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_ids' => 'required|array',
            'bank_ids.*' => 'exists:banks,id',
        ]);

        // Delete existing selections
        DB::table('user_banks')
            ->where('user_id', Auth::id())
            ->delete();

        // Insert new selections
        $inserts = collect($validated['bank_ids'])->map(function ($bankId) {
            return [
                'user_id' => Auth::id(),
                'bank_id' => $bankId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        if (!empty($inserts)) {
            DB::table('user_banks')->insert($inserts);
        }

        return response()->json([
            'message' => 'Banks updated successfully',
            'selected_count' => count($inserts),
        ]);
    }

    /**
     * Add a single bank to selection
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
        ]);

        // Check if already selected
        $exists = DB::table('user_banks')
            ->where('user_id', Auth::id())
            ->where('bank_id', $validated['bank_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Bank already selected'], 200);
        }

        DB::table('user_banks')->insert([
            'user_id' => Auth::id(),
            'bank_id' => $validated['bank_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Bank added successfully',
        ], 201);
    }

    /**
     * Remove a bank from selection
     */
    public function remove(int $bankId): JsonResponse
    {
        $deleted = DB::table('user_banks')
            ->where('user_id', Auth::id())
            ->where('bank_id', $bankId)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Bank not in your selection'], 404);
        }

        return response()->json([
            'message' => 'Bank removed successfully',
        ]);
    }

    /**
     * Get offers for user's selected banks
     */
    public function myOffers(Request $request): JsonResponse
    {
        $bankIds = DB::table('user_banks')
            ->where('user_id', Auth::id())
            ->pluck('bank_id');

        if ($bankIds->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'Select your banks to see available offers',
            ]);
        }

        $offers = BankOffer::with(['bank.logo', 'cardType'])
            ->whereIn('bank_id', $bankIds)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('created_at')
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
}
