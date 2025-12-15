<?php

use Illuminate\Support\Facades\Route;
use Modules\Location\Http\AdminControllers\LocationController;




Route::prefix('admin')->as('admin.')->group(function () {
  
    Route::prefix('location')->middleware('auth')->controller(LocationController::class)->group(function () {
        Route::get('create', 'create')->name('create-location')->can('create');
        Route::post('store', 'store')->name('store-location');
        Route::get('show/{id}', 'show')->name('show-location');
        Route::get('edit/{id}', 'edit')->name('edit-location')->can('edit');
        Route::put('update/{id}', 'update')->name('update-location')->can('update');
        Route::delete('destroy/{id}', 'destroy')->name('delete-location')->can('delete');


    });
});


?>
