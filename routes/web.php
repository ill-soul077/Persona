<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Welcome/Landing Page
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Unified Dashboard (Main Dashboard)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart.data');
    
    // Chatbot Page
    Route::view('/chatbot', 'chatbot.index')->name('chatbot');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
    
    // Calendar & Export
    Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/feed', [TaskController::class, 'calendarFeed'])->name('calendar.feed');
    Route::get('/export', [TaskController::class, 'export'])->name('export');
});

// Chat/AI API Routes (for AJAX)
Route::middleware(['auth'])->prefix('api/chat')->name('chat.')->group(function () {
    Route::post('/send', [ChatController::class, 'send'])->name('send');
    Route::post('/confirm-transaction', [ChatController::class, 'confirmTransaction'])->name('confirm.transaction');
    Route::post('/confirm-task', [ChatController::class, 'confirmTask'])->name('confirm.task');
    Route::post('/update-task', [ChatController::class, 'updateTask'])->name('update.task');
});

// Settings & Reports
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

// Authentication Routes
require __DIR__.'/auth.php';
