<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogCategory\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/blog-category')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{blogCategory}', FindController::class)->name('find');
        Route::get('/{blogCategory}/edit', EditController::class)->name('edit');
        Route::put('/{blogCategory}', UpdateController::class)->name('update');
        Route::delete('/{blogCategory}', DeleteController::class)->name('delete');
    })->name('blog-category');
