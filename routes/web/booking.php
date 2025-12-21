<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Booking;
Route::prefix('/booking')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', Booking\IndexController::class)->name('index');
    Route::get('/list', Booking\ListController::class)->name('list');
    Route::post('/{booking}/complete', Booking\CompleteController::class)->name('complete');
})->name('booking');
