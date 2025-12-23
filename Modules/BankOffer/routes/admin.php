<?php

use Illuminate\Support\Facades\Route;
use Modules\BankOffer\app\Http\Controllers\Admin\BankController;
use Modules\BankOffer\app\Http\Controllers\Admin\CardTypeController;
use Modules\BankOffer\app\Http\Controllers\Admin\BankOfferController;
use Modules\BankOffer\app\Http\Controllers\Admin\BankOfferRequestController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Banks Management
Route::prefix('banks')->name('banks.')->controller(BankController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{bank}', 'show')->name('show');
    Route::get('/{bank}/edit', 'edit')->name('edit');
    Route::put('/{bank}', 'update')->name('update');
    Route::delete('/{bank}', 'destroy')->name('destroy');
});

// Card Types Management
Route::prefix('card-types')->name('card-types.')->controller(CardTypeController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{cardType}', 'show')->name('show');
    Route::get('/{cardType}/edit', 'edit')->name('edit');
    Route::put('/{cardType}', 'update')->name('update');
    Route::delete('/{cardType}', 'destroy')->name('destroy');
});

// Bank Offers Management
Route::prefix('bank-offers')->name('bank-offers.')->controller(BankOfferController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{bankOffer}', 'show')->name('show');
    Route::put('/{bankOffer}/approve', 'approve')->name('approve');
    Route::put('/{bankOffer}/reject', 'reject')->name('reject');
    Route::put('/{bankOffer}/status', 'updateStatus')->name('status');
});

// Bank Offer Participation Requests
Route::prefix('bank-offer-requests')->name('bank-offer-requests.')->controller(BankOfferRequestController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{bankOfferBrand}', 'show')->name('show');
    Route::put('/{bankOfferBrand}/approve', 'approve')->name('approve');
    Route::put('/{bankOfferBrand}/reject', 'reject')->name('reject');
});
