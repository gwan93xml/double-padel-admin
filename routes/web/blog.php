<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Blog\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/blog')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{blog}', FindController::class)->name('find');
        Route::get('/{blog}/edit', EditController::class)->name('edit');
        Route::put('/{blog}', UpdateController::class)->name('update');
        Route::delete('/{blog}', DeleteController::class)->name('delete');
    })->name('blog');
