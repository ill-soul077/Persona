<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TaskAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected TaskAnalyticsService $taskAnalytics;

    public function __construct(TaskAnalyticsService $taskAnalytics)
    {
        $this->taskAnalytics = $taskAnalytics;
    }

    /**
     * Display the reports page with financial and task analytics data.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get date range from request or default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Current month stats
        $currentMonthIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
            
        $currentMonthExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
            
        $currentMonthNet = $currentMonthIncome - $currentMonthExpenses;
        $savingsRate = $currentMonthIncome > 0 ? ($currentMonthNet / $currentMonthIncome) * 100 : 0;
        
        // Last 6 months trend data
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTrend[] = [
                'month' => $date->format('M'),
                'income' => $user->transactions()
                    ->where('type', 'income')
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->sum('amount'),
                'expense' => $user->transactions()
                    ->where('type', 'expense')
                    ->whereMonth('date', $date->month)
                    ->whereYear('date', $date->year)
                    ->sum('amount')
            ];
        }
        
        // Expense breakdown by category (using polymorphic relationship)
        $expensesByCategory = DB::table('transactions')
            ->join('expense_categories', function($join) {
                $join->on('transactions.category_id', '=', 'expense_categories.id')
                     ->where('transactions.category_type', '=', 'App\\Models\\ExpenseCategory');
            })
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', Carbon::now()->month)
            ->whereYear('transactions.date', Carbon::now()->year)
            ->select('expense_categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderBy('total', 'desc')
            ->get();
            
        // Income breakdown by source (using polymorphic relationship)
        $incomeBySource = DB::table('transactions')
            ->join('income_sources', function($join) {
                $join->on('transactions.category_id', '=', 'income_sources.id')
                     ->where('transactions.category_type', '=', 'App\\Models\\IncomeSource');
            })
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', 'income')
            ->whereMonth('transactions.date', Carbon::now()->month)
            ->whereYear('transactions.date', Carbon::now()->year)
            ->select('income_sources.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('income_sources.id', 'income_sources.name')
            ->orderBy('total', 'desc')
            ->get();
        
        // Top 5 expenses
        $topExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->orderBy('amount', 'desc')
            ->take(5)
            ->get();
            
        // Yearly comparison
        $currentYearTotal = $user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
            
        $lastYearTotal = $user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', Carbon::now()->subYear()->year)
            ->sum('amount');
            
        $yearOverYearChange = $lastYearTotal > 0 
            ? (($currentYearTotal - $lastYearTotal) / $lastYearTotal) * 100 
            : 0;
        
        // Task Analytics Data
        $taskPeriod = $request->input('task_period', 30); // default 30 days
        $taskStartDate = $request->input('task_start_date', Carbon::now()->subDays($taskPeriod)->format('Y-m-d'));
        $taskEndDate = $request->input('task_end_date', Carbon::now()->format('Y-m-d'));
        
        // Get task metrics
        $taskMetrics = $this->taskAnalytics->getOverviewMetrics($user->id);
        
        // Get task completion trend
        $completionTrend = $this->taskAnalytics->getCompletionTrend($taskStartDate, $taskEndDate, $user->id);
        
        // Get priority distribution
        $priorityDistribution = $this->taskAnalytics->getPriorityDistribution($user->id, [
            'start_date' => $taskStartDate,
            'end_date' => $taskEndDate
        ]);
        
        // Get productivity heatmap
        $productivityHeatmap = $this->taskAnalytics->getProductivityHeatmap($taskPeriod, $user->id);
        
        // Get category breakdown
        $taskCategoryBreakdown = $this->taskAnalytics->getCategoryBreakdown($user->id, [
            'start_date' => $taskStartDate,
            'end_date' => $taskEndDate
        ]);
        
        // Get smart insights
        $smartInsights = $this->taskAnalytics->generateSmartInsights($user->id);
        
        return view('reports.index', compact(
            'currentMonthIncome',
            'currentMonthExpenses',
            'currentMonthNet',
            'savingsRate',
            'monthlyTrend',
            'expensesByCategory',
            'incomeBySource',
            'topExpenses',
            'currentYearTotal',
            'lastYearTotal',
            'yearOverYearChange',
            'startDate',
            'endDate',
            'taskMetrics',
            'completionTrend',
            'priorityDistribution',
            'productivityHeatmap',
            'taskCategoryBreakdown',
            'smartInsights',
            'taskStartDate',
            'taskEndDate',
            'taskPeriod'
        ));
    }
}
