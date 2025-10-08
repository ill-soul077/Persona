<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaskController;
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

// Task Module Routes
Route::middleware(['auth'])->prefix('tasks')->name('tasks.')->group(function () {
    // Task List & Views
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::get('/create', [TaskController::class, 'create'])->name('create');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/{task}', [TaskController::class, 'show'])->name('show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    
    // Task Actions
    Route::post('/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->name('toggle.status');
    Route::post('/quick-add', [TaskController::class, 'quickAdd'])->name('quick.add');
    
    // Calendar Feed (JSON)
    Route::get('/calendar/feed', [TaskController::class, 'calendarFeed'])->name('calendar.feed');
});

// Chat API Routes
Route::middleware(['auth'])->prefix('api/chat')->name('chat.')->group(function () {
    // Finance Parsing
    Route::post('/parse-finance', [ChatController::class, 'parseFinance'])->name('parse.finance');
    Route::post('/confirm-transaction', [ChatController::class, 'confirmTransaction'])->name('confirm.transaction');
    
    // Task Parsing
    Route::post('/parse-task', [ChatController::class, 'parseTask'])->name('parse.task');
    Route::post('/confirm-task', [ChatController::class, 'confirmTask'])->name('confirm.task');
    Route::post('/update-task', [ChatController::class, 'updateTask'])->name('update.task');
});

// Authentication routes (will be added later)
require __DIR__.'/auth.php';

