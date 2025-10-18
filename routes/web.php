<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Welcome/Landing Page (keep public landing available for tests)
Route::get('/', function () {
    return view('welcome');
});

// Unified Dashboard (Main Dashboard)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart.data');
    Route::post('/dashboard/budget/refresh', [DashboardController::class, 'refreshBudgetSummary'])->name('dashboard.budget.refresh');
    
    // Chatbot Routes
    Route::get('/chatbot', [\App\Http\Controllers\ChatbotController::class, 'index'])->name('chatbot');
    Route::post('/chatbot/process', [\App\Http\Controllers\ChatbotController::class, 'processMessage'])->name('chatbot.process');
    Route::post('/chatbot/confirm', [\App\Http\Controllers\ChatbotController::class, 'confirmTransaction'])->name('chatbot.confirm');
    Route::post('/chatbot/confirm-task', [\App\Http\Controllers\ChatbotController::class, 'confirmTask'])->name('chatbot.confirm-task');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Alias for dashboard link expecting profile.show
    Route::get('/profile/show', [ProfileController::class, 'edit'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Finance Module Routes
Route::middleware(['auth'])->prefix('finance')->name('finance.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/budget/refresh', [DashboardController::class, 'refreshBudgetSummary'])->name('dashboard.budget.refresh');
    
    // Transactions CRUD
    Route::resource('transactions', TransactionController::class);
    
    // AI Receipt Scanner
    Route::post('/transactions/scan-receipt', [TransactionController::class, 'scanReceipt'])->name('transactions.scan-receipt');
    
    // Chart Data & Analytics
    Route::get('/chart-data', [TransactionController::class, 'chartData'])->name('chart.data');
    Route::get('/category-drilldown', [TransactionController::class, 'categoryDrilldown'])->name('category.drilldown');

    // Budget Management
    Route::get('/budget', [\App\Http\Controllers\BudgetController::class, 'show'])->name('budget.show');
    Route::post('/budget', [\App\Http\Controllers\BudgetController::class, 'store'])->name('budget.store');
    Route::delete('/budget/{id}', [\App\Http\Controllers\BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::get('/budget/insights', [\App\Http\Controllers\BudgetController::class, 'insights'])->name('budget.insights');

    // Reports (alias within finance namespace for dashboard link compatibility)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
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
    Route::post('/parse-finance', [ChatController::class, 'parseFinance'])->name('parse.finance');
    Route::post('/confirm-transaction', [ChatController::class, 'confirmTransaction'])->name('confirm.transaction');
    Route::post('/confirm-task', [ChatController::class, 'confirmTask'])->name('confirm.task');
    Route::post('/update-task', [ChatController::class, 'updateTask'])->name('update.task');
});

// Settings & Reports
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/security', [SettingsController::class, 'updateSecurity'])->name('settings.security.update');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
    Route::post('/settings/apps/connect', [SettingsController::class, 'connectApp'])->name('settings.apps.connect');
    Route::post('/settings/apps/disconnect', [SettingsController::class, 'disconnectApp'])->name('settings.apps.disconnect');
    Route::get('/settings/export', [SettingsController::class, 'exportData'])->name('settings.export');
});

// Authentication Routes
require __DIR__.'/auth.php';
