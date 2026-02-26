<?php

use App\Http\Controllers\UpdateCategory\{
    CreateController,
    DeleteController,
    EditController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
};
use Illuminate\Support\Facades\Route;

Route::prefix('/update-category')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{updateCategory}', FindController::class)->name('find');
        Route::get('/{updateCategory}/edit', EditController::class)->name('edit');
        Route::put('/{updateCategory}', UpdateController::class)->name('update');
        Route::delete('/{updateCategory}', DeleteController::class)->name('delete');
    })->name('update-category');
