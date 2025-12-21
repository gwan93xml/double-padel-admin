<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethod\{
    DeleteController,
    FindController,
    IndexController,
    ListController,
    StoreController,
    UpdateController,
    CreateController,
    EditController,
};

Route::prefix('/payment-method')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/', IndexController::class)->name('index');
        Route::get('/list', ListController::class)->name('list');
        Route::get('/create', CreateController::class)->name('create');
        Route::post('/', StoreController::class)->name('store');
        Route::get('/{paymentMethod}', FindController::class)->name('find');
        Route::get('/{paymentMethod}/edit', EditController::class)->name('edit');
        Route::put('/{paymentMethod}', UpdateController::class)->name('update');
        Route::delete('/{paymentMethod}', DeleteController::class)->name('delete');
    })->name('payment-method');
