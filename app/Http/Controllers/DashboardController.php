<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Task;
use App\Models\AiLog;
use App\Models\Budget;
use App\Models\BudgetSummary;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the unified dashboard.
     */
    public function index()
    {
    /** @var \App\Models\User|null $authUser */
    $userId = Auth::id();
        
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
        
        // Current month's budget
    /** @var \App\Models\User|null $authUser */
    $authUser = Auth::user();
    $currentBudget = $authUser ? $authUser->currentBudget() : null;
        $budgetData = null;
        
        if ($currentBudget) {
            $budgetData = [
                'amount' => $currentBudget->amount,
                'spent' => $currentBudget->total_spent,
                'remaining' => $currentBudget->remaining,
                'percentage' => round($currentBudget->percentage_used, 1),
                'status' => $currentBudget->status_color,
                'is_exceeded' => $currentBudget->isExceeded(),
            ];
        }

        // Load existing AI budget summary from DB if available
        if ($authUser) {
            $monthDate = sprintf('%04d-%02d-01', now()->year, now()->month);
            $existingSummary = BudgetSummary::where('user_id', $authUser->id)
                ->where('month', $monthDate)
                ->first();
            
            if ($existingSummary && $existingSummary->isFresh()) {
                session()->flash('budget_ai_summary', $existingSummary->summary_data);
            }
        }
        
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
            'weeklyTrend',
            'budgetData'
        ));
    }

    /**
     * Refresh the monthly budget AI summary using Gemini and redirect back.
     * Implements DB caching, throttling (1/hour per user), and smart fallback.
     */
    public function refreshBudgetSummary(Request $request, GeminiService $gemini)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $year = now()->year;
        $month = now()->month;
        $monthDate = sprintf('%04d-%02d-01', $year, $month);

        // Check if we have a recent summary in DB (within last hour = throttle)
        $existingSummary = BudgetSummary::where('user_id', $user->id)
            ->where('month', $monthDate)
            ->first();

        if ($existingSummary && $existingSummary->updated_at->diffInMinutes(now()) < 60) {
            // Throttle: don't regenerate if refreshed < 1 hour ago
            Log::info('Budget summary throttled - using cached DB result', [
                'user_id' => $user->id,
                'month' => $monthDate,
                'age_minutes' => $existingSummary->updated_at->diffInMinutes(now())
            ]);
            
            $data = $existingSummary->summary_data;
            if ($existingSummary->is_fallback) {
                $data['fallback'] = true;
            }
            
            return back()->with([
                'budget_ai_summary' => $data,
                'budget_ai_summary_throttled' => true
            ]);
        }

        // Gather current month context
        $budget = $user->budgets()->whereYear('month', $year)->whereMonth('month', $month)->first();

        $spentThisMonth = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $incomeThisMonth = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $daysLeft = now()->endOfMonth()->diffInDays(now());

        // Category breakdown for advice context
        $categoryBreakdown = DB::table('transactions as t')
            ->leftJoin('expense_categories as c', 't.category_id', '=', 'c.id')
            ->selectRaw("COALESCE(c.name, 'Uncategorized') as category, SUM(t.amount) as total")
            ->where('t.user_id', $user->id)
            ->where('t.type', 'expense')
            ->whereYear('t.date', $year)
            ->whereMonth('t.date', $month)
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->toArray();

        $context = [
            'month_name' => now()->format('F Y'),
            'currency' => $budget->currency ?? 'BDT',
            'budget_amount' => (float) ($budget->amount ?? 0),
            'total_spent' => (float) $spentThisMonth,
            'remaining' => (float) (($budget->amount ?? 0) - $spentThisMonth),
            'days_left' => $daysLeft,
            'income' => (float) $incomeThisMonth,
            'category_breakdown' => $categoryBreakdown,
        ];

        try {
            // Set a hard timeout for AI generation to prevent PHP execution limit
            set_time_limit(40); // 40s max for this operation
            
            $advice = $gemini->generateBudgetAdvice($context);
            $modelUsed = $this->getGeminiModelName($gemini);
            $isFallback = !empty($advice['fallback']);

            // Save or update in DB
            BudgetSummary::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'month' => $monthDate,
                ],
                [
                    'summary_data' => $advice,
                    'model_used' => $modelUsed,
                    'is_fallback' => $isFallback,
                ]
            );

            // Log the AI interaction only if not fallback
            if (!$isFallback) {
                AiLog::create([
                    'user_id' => $user->id,
                    'module' => 'finance',
                    'raw_text' => json_encode($context),
                    'parsed_json' => $advice,
                    'model' => $modelUsed,
                    'status' => 'parsed',
                    'ip_address' => $request->ip(),
                ]);
            }

            Log::info('Budget summary generated', [
                'user_id' => $user->id,
                'model' => $modelUsed,
                'is_fallback' => $isFallback
            ]);

            return back()->with('budget_ai_summary', $advice);
        } catch (\Throwable $e) {
            Log::error('Budget summary generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ]);
            
            // On timeout or any failure, use heuristic fallback
            if (str_contains($e->getMessage(), 'Maximum execution time') || 
                str_contains($e->getMessage(), 'timeout')) {
                Log::warning('Timeout detected, using heuristic fallback');
                
                $fallback = app(GeminiService::class)->generateHeuristicBudgetAdvice($context);
                
                BudgetSummary::updateOrCreate(
                    ['user_id' => $user->id, 'month' => $monthDate],
                    ['summary_data' => $fallback, 'model_used' => 'heuristic-fallback', 'is_fallback' => true]
                );
                
                return back()->with('budget_ai_summary', $fallback);
            }
            
            return back()->with('budget_ai_summary_error', $e->getMessage());
        } finally {
            // Reset time limit
            set_time_limit(60);
        }
    }

    private function getGeminiModelName(GeminiService $g): string
    {
        try {
            $ref = new \ReflectionClass($g);
            $prop = $ref->getProperty('model');
            $prop->setAccessible(true);
            return (string) $prop->getValue($g);
        } catch (\Throwable $e) {
            return 'unknown-model';
        }
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
    $userId = Auth::id();
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
