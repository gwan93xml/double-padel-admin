<?php

use App\Http\Controllers\HomeNavigation\{
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

Route::prefix('/home-navigation')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{homeNavigation}', FindController::class)->name('find');
        Route::get('/{homeNavigation}/edit', EditController::class)->name('edit');
        Route::put('/{homeNavigation}', UpdateController::class)->name('update');
        Route::delete('/{homeNavigation}', DeleteController::class)->name('delete');
    })->name('home-navigation');
