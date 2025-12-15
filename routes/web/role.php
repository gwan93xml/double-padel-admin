<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Role\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/role')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', IndexController::class)->name('index');
    Route::get('/list', ListController::class)->name('list');
    Route::get('/create', CreateController::class)->name('create');
    Route::post('/', StoreController::class)->name('store');
    Route::get('/{role}', FindController::class)->name('find');
    Route::get('/{role}/edit', EditController::class)->name('edit');
    Route::put('/{role}', UpdateController::class)->name('update');
    Route::delete('/{role}', DeleteController::class)->name('delete');
})->name('role');
