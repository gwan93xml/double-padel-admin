<?php

use App\Http\Controllers\Update\{
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

Route::prefix('/update')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{update}', FindController::class)->name('find');
        Route::get('/{update}/edit', EditController::class)->name('edit');
        Route::put('/{update}', UpdateController::class)->name('update');
        Route::delete('/{update}', DeleteController::class)->name('delete');
    })->name('update');
