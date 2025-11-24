<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Tag\Http\AdminControllers\TagsController;

Route::prefix('admin')->as('admin.')->group(function () {


    Route::prefix('tag')->as('tag.')->middleware('auth')->controller(TagsController::class)->group(function () {

        Route::get('/', 'index')->name('tag-list')->can('admin_dashboard', User::class);
        Route::get('create', 'create')->name('create-tag');
        Route::post('store', 'store')->name('store-tag');
        Route::get('show/{id}', 'show')->name('show-tag');
        Route::get('edit/{id}', 'edit')->name('edit-tag');
        Route::post('update/{id}', 'update')->name('update-tag');
        Route::post('destroy/{id}', 'destroy')->name('delete-tag');

    });
});


?>
