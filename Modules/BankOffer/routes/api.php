<?php

use Illuminate\Support\Facades\Route;
use Modules\BankOffer\app\Http\Controllers\Api\MobileBankOfferController;
use Modules\BankOffer\app\Http\Controllers\Api\RetailerBankOfferController;
use Modules\BankOffer\app\Http\Controllers\Api\BankPortalOfferController;
use Modules\BankOffer\app\Http\Controllers\Api\UserBankController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes - Mobile App
Route::prefix('v1')->group(function () {
    // Bank offers for mobile app
    Route::get('bank-offers', [MobileBankOfferController::class, 'index']);
    Route::get('bank-offers/{bankOffer}', [MobileBankOfferController::class, 'show']);

    // Banks with offers
    Route::get('banks', [MobileBankOfferController::class, 'banks']);
    Route::get('banks/{bank}/offers', [MobileBankOfferController::class, 'offersByBank']);

    // Brand offers (combined with bank offers)
    Route::get('brands/{brand}/offers', [MobileBankOfferController::class, 'brandOffers']);

    // Card BIN lookup - identify card type and bank from first 6 digits
    Route::post('card-lookup', [MobileBankOfferController::class, 'cardLookup']);
});

// Authenticated routes
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // User's selected banks (like interests)
    Route::prefix('my-banks')->group(function () {
        Route::get('/', [UserBankController::class, 'index']);           // Get selected banks
        Route::put('/', [UserBankController::class, 'update']);          // Update all selections
        Route::post('/add', [UserBankController::class, 'add']);         // Add single bank
        Route::delete('/{bankId}', [UserBankController::class, 'remove']); // Remove single bank
        Route::get('/offers', [UserBankController::class, 'myOffers']);  // Get offers for my banks
    });

    // Retailer routes
    Route::prefix('retailer')->group(function () {
        Route::get('bank-offers', [RetailerBankOfferController::class, 'index']);
        Route::get('bank-offers/{bankOffer}', [RetailerBankOfferController::class, 'show']);
        Route::post('bank-offers/{bankOffer}/request', [RetailerBankOfferController::class, 'requestParticipation']);
        Route::get('bank-offer-requests', [RetailerBankOfferController::class, 'myRequests']);
        Route::delete('bank-offer-requests/{bankOfferBrand}', [RetailerBankOfferController::class, 'cancelRequest']);
    });

    // Bank portal routes
    Route::prefix('bank')->group(function () {
        Route::get('offers', [BankPortalOfferController::class, 'index']);
        Route::post('offers', [BankPortalOfferController::class, 'store']);
        Route::get('offers/{bankOffer}', [BankPortalOfferController::class, 'show']);
        Route::put('offers/{bankOffer}', [BankPortalOfferController::class, 'update']);
        Route::delete('offers/{bankOffer}', [BankPortalOfferController::class, 'destroy']);
        Route::get('offers/{bankOffer}/requests', [BankPortalOfferController::class, 'participationRequests']);
    });
});
