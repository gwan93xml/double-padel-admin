<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/user')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('/list', ListController::class)->name('list');
    Route::get('/create', CreateController::class)->name('create');
    Route::post('/', StoreController::class)->name('store');
    Route::get('/{user}', FindController::class)->name('find');
    Route::get('/{user}/edit', EditController::class)->name('edit');
    Route::put('/{user}', UpdateController::class)->name('update');
    Route::delete('/{user}', DeleteController::class)->name('delete');
})->name('user');
