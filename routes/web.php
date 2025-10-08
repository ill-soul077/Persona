<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('finance.dashboard');
});

// Finance Module Routes
Route::middleware(['auth'])->prefix('finance')->name('finance.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('dashboard');
    
    // Transactions CRUD
    Route::resource('transactions', TransactionController::class);
    
    // Chart Data & Analytics
    Route::get('/chart-data', [TransactionController::class, 'chartData'])->name('chart.data');
    Route::get('/category-drilldown', [TransactionController::class, 'categoryDrilldown'])->name('category.drilldown');
});

// Chat API Routes
Route::middleware(['auth'])->prefix('api/chat')->name('chat.')->group(function () {
    Route::post('/parse-finance', [ChatController::class, 'parseFinance'])->name('parse.finance');
    Route::post('/parse-task', [ChatController::class, 'parseTask'])->name('parse.task');
    Route::post('/confirm-transaction', [ChatController::class, 'confirmTransaction'])->name('confirm.transaction');
});

// Authentication routes (will be added later)
require __DIR__.'/auth.php';
