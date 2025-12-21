<?php

use App\Http\Controllers\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/upload/image', ImageUploadController::class)->name('upload.image');
});
