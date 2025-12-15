<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Action\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
    SearchByCodeController,
    SearchByNameController
};

Route::prefix('/action')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::get('/search-by-code', SearchByCodeController::class)->name('search-by-code');
        Route::get('/search-by-name', SearchByNameController::class)->name('search-by-name');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{action}', FindController::class)->name('find');
        Route::get('/{action}/edit', EditController::class)->name('edit');
        Route::put('/{action}', UpdateController::class)->name('update');
        Route::delete('/{action}', DeleteController::class)->name('delete');
    })->name('action');
