<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Court\{
    DeleteController,
    IndexController,
    ListController,
    CreateController,
    StoreController,
    EditController,
    UpdateController,
};

Route::prefix('/court')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{court}/edit', EditController::class)->name('edit');
        Route::put('/{court}', UpdateController::class)->name('update');
        Route::delete('/{court}', DeleteController::class)->name('destroy');
    });
