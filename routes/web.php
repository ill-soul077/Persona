<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\FocusSessionController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Landing Page - Redirect to dashboard if authenticated
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('landing');

// Unified Dashboard (Main Dashboard)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart.data');
    Route::post('/dashboard/budget/refresh', [DashboardController::class, 'refreshBudgetSummary'])->name('dashboard.budget.refresh');
    
    // Chatbot Routes
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot');
    Route::post('/chatbot/process', [ChatbotController::class, 'processMessage'])->name('chatbot.process');
    Route::post('/chatbot/confirm', [ChatbotController::class, 'confirmTransaction'])->name('chatbot.confirm');
    Route::post('/chatbot/confirm-task', [ChatbotController::class, 'confirmTask'])->name('chatbot.confirm-task');
    
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
    Route::get('/budget', [BudgetController::class, 'show'])->name('budget.show');
    Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
    Route::delete('/budget/{id}', [BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::get('/budget/insights', [BudgetController::class, 'insights'])->name('budget.insights');

    // Reports (alias within finance namespace for dashboard link compatibility)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
});

// Task Module Routes
Route::middleware(['auth'])->prefix('tasks')->name('tasks.')->group(function () {
    // Task List & Views
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::get('/create', [TaskController::class, 'create'])->name('create');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('/{task}', [TaskController::class, 'show'])->whereNumber('task')->name('show');
    Route::get('/{task}/edit', [TaskController::class, 'edit'])->whereNumber('task')->name('edit');
    Route::put('/{task}', [TaskController::class, 'update'])->whereNumber('task')->name('update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->whereNumber('task')->name('destroy');
    
    // Task Actions
    Route::post('/{task}/toggle-status', [TaskController::class, 'toggleStatus'])->whereNumber('task')->name('toggle.status');
    Route::post('/quick-add', [TaskController::class, 'quickAdd'])->name('quick.add');
    
    // Calendar & Export
    Route::get('/calendar', [TaskController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/feed', [TaskController::class, 'calendarFeed'])->name('calendar.feed');
    Route::get('/export', [TaskController::class, 'export'])->name('export');
});

// Focus Mode Routes
Route::middleware(['auth'])->prefix('focus')->name('focus.')->group(function () {
    Route::get('/', [FocusSessionController::class, 'index'])->name('index');
    Route::get('/analytics', [FocusSessionController::class, 'analytics'])->name('analytics');
    Route::post('/sessions/start', [FocusSessionController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/complete', [FocusSessionController::class, 'complete'])->name('sessions.complete');
    Route::get('/sessions/history', [FocusSessionController::class, 'history'])->name('sessions.history');
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

// Task Templates
Route::middleware(['auth'])->prefix('templates')->name('templates.')->group(function () {
    Route::get('/', [TaskTemplateController::class, 'index'])->name('index');
    Route::get('/create', [TaskTemplateController::class, 'create'])->name('create');
    Route::post('/', [TaskTemplateController::class, 'store'])->name('store');
    Route::get('/{template}', [TaskTemplateController::class, 'show'])->name('show');
    Route::get('/{template}/edit', [TaskTemplateController::class, 'edit'])->name('edit');
    Route::put('/{template}', [TaskTemplateController::class, 'update'])->name('update');
    Route::delete('/{template}', [TaskTemplateController::class, 'destroy'])->name('destroy');
    Route::post('/{template}/apply', [TaskTemplateController::class, 'apply'])->name('apply');
    Route::get('/api/suggestions', [TaskTemplateController::class, 'suggestions'])->name('suggestions');
});

// Authentication Routes
require __DIR__.'/auth.php';
