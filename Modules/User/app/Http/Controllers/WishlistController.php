<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\User\app\Http\Requests\WishlistRequest;
use Modules\User\Services\UserService;
use Modules\Business\app\Http\Resources\BrandResource;

class WishlistController extends Controller
{
    /**
     * Add shop to user wishlist
     */
    public function addToWishlist(WishlistRequest $request, UserService $userService)
    {
        try {
            $userService
                ->setInputs($request->validated())
                ->setAuthUser(Auth::user())
                ->addBrandToWishlist()
                ->collectOutput('data', $data);

            return response()->json([
                'message' => __('validation.add shop to wishlist')
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove shop from user wishlist
     */
    public function removeFromWishlist(WishlistRequest $request, UserService $userService)
    {
        try {
            $userService
                ->setInputs($request->validated())
                ->setAuthUser(Auth::user())
                ->removeBrandFromWishlist();

            return response()->json([
                'message' => __('validation.removed shop from wishlist')
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get all items in user's wishlist
     */
    public function getAllUserWishlist(UserService $userService)
    {
        try {
            $userService
                ->setAuthUser(Auth::user())
                ->getAllUserWishlist()
                ->collectOutput('wishlist', $wishlist);

            // Load relationships needed for BrandResource
            $wishlist->load(['categories', 'branches', 'activeOffers', 'activeBankOfferBrands.bankOffer.bank']);

            return response()->json([
                'data' => BrandResource::collection($wishlist)
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
