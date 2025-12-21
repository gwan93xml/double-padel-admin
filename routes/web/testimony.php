<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Testimony\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/testimony')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{testimony}', FindController::class)->name('find');
        Route::get('/{testimony}/edit', EditController::class)->name('edit');
        Route::put('/{testimony}', UpdateController::class)->name('update');
        Route::delete('/{testimony}', DeleteController::class)->name('delete');
    })->name('testimony');
