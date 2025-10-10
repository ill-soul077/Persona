<?php

require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\Transaction;
use App\Models\Task;
use App\Models\AiLog;

$app = \Illuminate\Foundation\Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
        //
    })
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        //
    })->create();

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test database connection and data
try {
    echo "Testing Dashboard Data Sources:\n\n";

    // Check if we have any users
    $userCount = User::count();
    echo "Users in database: $userCount\n";

    if ($userCount > 0) {
        $testUser = User::first();
        echo "Test user: {$testUser->name} (ID: {$testUser->id})\n\n";

        // Test Transaction data (what DashboardController fetches)
        $totalIncome = Transaction::where('user_id', $testUser->id)
            ->where('type', 'income')
            ->sum('amount');
        
        $totalExpense = Transaction::where('user_id', $testUser->id)
            ->where('type', 'expense')
            ->sum('amount');
        
        $balance = $totalIncome - $totalExpense;
        
        echo "Financial Data for user {$testUser->id}:\n";
        echo "- Total Income: $totalIncome\n";
        echo "- Total Expense: $totalExpense\n";
        echo "- Balance: $balance\n\n";

        // Monthly data
        $monthlyIncome = Transaction::where('user_id', $testUser->id)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $testUser->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        echo "Current Month Data:\n";
        echo "- Monthly Income: $monthlyIncome\n";
        echo "- Monthly Expense: $monthlyExpense\n\n";

        // Recent transactions
        $recentTransactions = Transaction::where('user_id', $testUser->id)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        echo "Recent Transactions (last 5):\n";
        foreach ($recentTransactions as $transaction) {
            $categoryName = $transaction->category?->name ?? 'Uncategorized';
            echo "- {$transaction->type}: {$transaction->amount} ({$categoryName}) - {$transaction->date}\n";
        }

        // Task data
        $tasksDueToday = Task::where('user_id', $testUser->id)->whereDate('due_date', today())->count();
        $tasksOverdue = Task::where('user_id', $testUser->id)->where('due_date', '<', today())->where('status', '!=', 'completed')->count();
        $tasksCompleted = Task::where('user_id', $testUser->id)
            ->whereMonth('completed_at', now()->month)
            ->count();

        echo "\nTask Data:\n";
        echo "- Tasks due today: $tasksDueToday\n";
        echo "- Tasks overdue: $tasksOverdue\n";
        echo "- Tasks completed this month: $tasksCompleted\n\n";

        // AI Logs
        $aiLogCount = AiLog::where('user_id', $testUser->id)->count();
        echo "AI Logs count: $aiLogCount\n";

    } else {
        echo "No users found in database. Please run seeders first.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}