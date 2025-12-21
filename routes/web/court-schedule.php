<?php

use App\Http\Controllers\CourtSchedule\IndexController;
use App\Http\Controllers\CourtSchedule\ListController;
use App\Http\Controllers\CourtSchedule\CreateController;
use App\Http\Controllers\CourtSchedule\StoreController;
use App\Http\Controllers\CourtSchedule\EditController;
use App\Http\Controllers\CourtSchedule\UpdateController;
use App\Http\Controllers\CourtSchedule\DeleteController;
use App\Http\Controllers\CourtSchedule\BulkGenerateShowController;
use App\Http\Controllers\CourtSchedule\BulkGenerateStoreController;
use App\Http\Controllers\CourtSchedule\CalendarController;
use App\Http\Controllers\CourtSchedule\YearController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('court-schedule')->group(function () {
        Route::get('/', IndexController::class)->name('court-schedule.index');
        Route::get('/list', ListController::class)->name('court-schedule.list');
        Route::get('/calendar', CalendarController::class)->name('court-schedule.calendar');
        Route::get('/year', YearController::class)->name('court-schedule.year');
        Route::get('/create', CreateController::class)->name('court-schedule.create');
        Route::post('/', StoreController::class)->name('court-schedule.store');
        Route::get('/bulk-generate', BulkGenerateShowController::class)->name('court-schedule.bulk-generate');
        Route::post('/bulk-generate', BulkGenerateStoreController::class)->name('court-schedule.bulk-generate.store');
        Route::get('/{courtSchedule}/edit', EditController::class)->name('court-schedule.edit');
        Route::put('/{courtSchedule}', UpdateController::class)->name('court-schedule.update');
        Route::delete('/{courtSchedule}', DeleteController::class)->name('court-schedule.delete');
    });
});

