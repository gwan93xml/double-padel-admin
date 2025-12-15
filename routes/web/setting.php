<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Setting\{
    IndexController,
    StoreController
};

Route::prefix('/setting')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', IndexController::class);
    Route::post('/', StoreController::class);
})->name('setting');
