<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Venue\{
    DeleteController,
    IndexController,
    CreateController,
    StoreController,
    EditController,
    UpdateController,
    ListController,
};

Route::prefix('/venue')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{venue}/edit', EditController::class)->name('edit');
        Route::put('/{venue}', UpdateController::class)->name('update');
        Route::delete('/{venue}', DeleteController::class)->name('destroy');
    });
