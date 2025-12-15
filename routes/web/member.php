<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member;
Route::prefix('/member')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', Member\IndexController::class)->name('index');
    Route::get('/list', Member\ListController::class)->name('list');
    Route::get('/{member}', Member\FindController::class)->name('find');
    Route::delete('/{member}', Member\DeleteController::class)->name('delete');
})->name('member');
