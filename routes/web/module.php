<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Module\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/module')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('/list', ListController::class)->name('list');
    Route::get('/create', CreateController::class)->name('create');
    Route::post('/', StoreController::class)->name('store');
    Route::get('/{module}', FindController::class)->name('find');
    Route::get('/{module}/edit', EditController::class)->name('edit');
    Route::put('/{module}', UpdateController::class)->name('update');
    Route::delete('/{module}', DeleteController::class)->name('delete');
})->name('module');
