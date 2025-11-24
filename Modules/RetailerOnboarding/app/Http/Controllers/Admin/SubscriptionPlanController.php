<?php

namespace Modules\RetailerOnboarding\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class SubscriptionPlanController extends Controller
{
    /**
     * List all subscription plans
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::all();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Create a new subscription plan
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'offers_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'duration_months' => 'required|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $plan = SubscriptionPlan::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan created successfully',
                'data' => $plan
            ], 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific subscription plan
     */
    public function show(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Update a subscription plan
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:50',
            'offers_count' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'billing_period' => 'sometimes|in:monthly,yearly',
            'duration_months' => 'sometimes|integer|min:1',
            'trial_days' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan updated successfully',
                'data' => $plan
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subscription plan
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
