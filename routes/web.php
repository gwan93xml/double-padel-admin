<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // IP Management Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('allowed-ips', App\Http\Controllers\AllowedIpController::class);
        Route::post('allowed-ips/{allowedIp}/toggle', [App\Http\Controllers\AllowedIpController::class, 'toggle'])->name('allowed-ips.toggle');
        Route::post('allowed-ips/add-current', [App\Http\Controllers\AllowedIpController::class, 'addCurrentIp'])->name('allowed-ips.add-current');
        Route::delete('allowed-ips/bulk/inactive', [App\Http\Controllers\AllowedIpController::class, 'bulkDeleteInactive'])->name('allowed-ips.bulk-delete-inactive');
        
        // Audit Routes
        Route::get('/audits', [App\Http\Controllers\AuditController::class, 'index'])->name('audits.index');
        Route::get('/audits/statistics', [App\Http\Controllers\AuditController::class, 'statistics'])->name('audits.statistics');
        Route::get('/audits/bulk', [App\Http\Controllers\AuditController::class, 'bulk'])->name('audits.bulk');
        Route::get('/audits/detail', [App\Http\Controllers\AuditController::class, 'detail'])->name('audits.detail');
    });
    
    // Stock Analysis Routes
    Route::prefix('admin/stock-analysis')->group(function () {
        Route::get('/item/{itemId}/actual-stock', [App\Http\Controllers\StockAnalysisController::class, 'showActualStock'])->name('stock-analysis.actual-stock');
        Route::get('/comparison', [App\Http\Controllers\StockAnalysisController::class, 'stockComparison'])->name('stock-analysis.comparison');
        Route::post('/bulk-actual-stock', [App\Http\Controllers\StockAnalysisController::class, 'bulkActualStock'])->name('stock-analysis.bulk-actual-stock');
        Route::get('/variance-report', [App\Http\Controllers\StockAnalysisController::class, 'stockVarianceReport'])->name('stock-analysis.variance-report');
        Route::post('/item/{itemId}/reconcile', [App\Http\Controllers\StockAnalysisController::class, 'reconcileStock'])->name('stock-analysis.reconcile');
    });
});

//require all file inside web folder
foreach (glob(__DIR__ . '/web/*.php') as $filename) {
    require $filename;
}

require __DIR__ . '/auth.php';
