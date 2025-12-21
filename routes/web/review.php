<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Review\{
    DeleteController,
    IndexController,
    ListController,
};

Route::prefix('/review')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::delete('/{review}', DeleteController::class)->name('delete');
    })->name('review');
