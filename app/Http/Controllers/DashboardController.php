<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Task;
use App\Models\AiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the unified dashboard.
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Financial Summary
        $balance = $this->getBalance($userId);
        $monthlyExpenses = $this->getMonthlyExpenses($userId);
        $monthlyIncome = $this->getMonthlyIncome($userId);
        
        // Task Summary
        $tasksDueToday = Task::where('user_id', $userId)->dueToday()->count();
        $tasksOverdue = Task::where('user_id', $userId)->overdue()->count();
        $tasksCompleted = Task::where('user_id', $userId)
            ->whereMonth('completed_at', now()->month)
            ->count();
        
        // Recent Activity
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();
            
        $recentTasks = Task::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();
            
        $recentAiLogs = AiLog::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();
        
        // Expense Distribution (for pie chart)
        $expenseDistribution = $this->getExpenseDistribution($userId);
        
        // Trends (last 7 days)
        $weeklyTrend = $this->getWeeklyTrend($userId);
        
        // Alias variables for view compatibility
        $totalIncome = $monthlyIncome;
        $totalExpenses = $monthlyExpenses;
        
        return view('dashboard', compact(
            'balance',
            'monthlyExpenses',
            'monthlyIncome',
            'totalIncome',
            'totalExpenses',
            'tasksDueToday',
            'tasksOverdue',
            'tasksCompleted',
            'recentTransactions',
            'recentTasks',
            'recentAiLogs',
            'expenseDistribution',
            'weeklyTrend'
        ));
    }
    
    /**
     * Get current balance.
     */
    protected function getBalance($userId): float
    {
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');
            
        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');
            
        return $income - $expense;
    }
    
    /**
     * Get current month expenses.
     */
    protected function getMonthlyExpenses($userId): float
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }
    
    /**
     * Get current month income.
     */
    protected function getMonthlyIncome($userId): float
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
    }
    
    /**
     * Get expense distribution by category.
     */
    protected function getExpenseDistribution($userId): array
    {
        $expenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->with('category')
            ->get()
            ->groupBy('category_id');
        
        $total = $expenses->sum(function($group) {
            return $group->sum('amount');
        });
        
        $distribution = [];
        $colors = [
            '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
        ];
        
        $colorIndex = 0;
        foreach ($expenses as $categoryId => $transactions) {
            $amount = $transactions->sum('amount');
            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
            
            $distribution[] = [
                'label' => $transactions->first()->category->name ?? 'Other',
                'value' => $amount,
                'color' => $colors[$colorIndex % count($colors)],
                'percentage' => round($percentage, 1),
                'count' => $transactions->count(),
            ];
            
            $colorIndex++;
        }
        
        // Sort by value descending
        usort($distribution, function($a, $b) {
            return $b['value'] <=> $a['value'];
        });
        
        return $distribution;
    }
    
    /**
     * Get weekly spending trend.
     */
    protected function getWeeklyTrend($userId): array
    {
        $days = [];
        $expenses = [];
        $income = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            
            $dayExpense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereDate('date', $date)
                ->sum('amount');
                
            $dayIncome = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereDate('date', $date)
                ->sum('amount');
            
            $expenses[] = $dayExpense;
            $income[] = $dayIncome;
        }
        
        return [
            'labels' => $days,
            'expenses' => $expenses,
            'income' => $income,
        ];
    }
    
    /**
     * Get chart data for AJAX requests.
     */
    public function chartData(Request $request)
    {
        $userId = auth()->id();
        $type = $request->get('type', 'expense-distribution');
        
        switch ($type) {
            case 'expense-distribution':
                return response()->json($this->getExpenseDistribution($userId));
                
            case 'weekly-trend':
                return response()->json($this->getWeeklyTrend($userId));
                
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }
}
