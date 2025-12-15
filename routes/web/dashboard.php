<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\IndexController;

Route::prefix('/')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', IndexController::class)->name('home');
    Route::get('/dashboard', IndexController::class)->name('dashboard');
});
