<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Get or create budget for a specific month.
     */
    public function show(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        $budget = Budget::where('user_id', $user->id)
            ->forMonth($year, $month)
            ->first();
            
        if (!$budget) {
            return response()->json([
                'exists' => false,
                'month' => Carbon::create($year, $month, 1)->format('F Y'),
            ]);
        }
        
        return response()->json([
            'exists' => true,
            'budget' => [
                'id' => $budget->id,
                'amount' => $budget->amount,
                'currency' => $budget->currency,
                'notes' => $budget->notes,
                'month' => $budget->month_name,
                'total_spent' => $budget->total_spent,
                'remaining' => $budget->remaining,
                'percentage_used' => round($budget->percentage_used, 1),
                'status' => $budget->status_color,
                'is_exceeded' => $budget->isExceeded(),
                'is_near_limit' => $budget->isNearLimit(),
            ]
        ]);
    }

    /**
     * Store or update monthly budget.
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:500',
            'apply_to_future' => 'nullable|boolean',
        ]);

        // Convert YYYY-MM to first day of month
        $monthDate = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        
        // Create or update budget for specified month
        $budget = Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $monthDate,
            ],
            [
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'USD',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Apply to future months if requested
        if ($request->input('apply_to_future', false)) {
            $this->applyToFutureMonths($user, $monthDate, $validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget saved successfully!',
            'budget' => [
                'id' => $budget->id,
                'amount' => $budget->amount,
                'currency' => $budget->currency,
                'month' => $budget->month_name,
            ]
        ]);
    }

    /**
     * Apply budget to future months.
     */
    protected function applyToFutureMonths(User $user, Carbon $startMonth, array $data)
    {
        $currentMonth = $startMonth->copy()->addMonth();
        $endMonth = now()->addMonths(12); // Apply to next 12 months
        
        while ($currentMonth->lte($endMonth)) {
            Budget::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'month' => $currentMonth->copy(),
                ],
                [
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'notes' => $data['notes'] ?? null,
                ]
            );
            
            $currentMonth->addMonth();
        }
    }

    /**
     * Delete a budget.
     */
    public function destroy($id)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $budget = Budget::where('user_id', $user->id)
            ->findOrFail($id);
            
        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully!'
        ]);
    }

    /**
     * Get budget statistics and insights.
     */
    public function insights()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $currentBudget = $user->currentBudget();
        
        if (!$currentBudget) {
            return response()->json([
                'has_budget' => false,
                'message' => 'No budget set for current month',
            ]);
        }

        // Calculate insights
        $daysInMonth = now()->daysInMonth;
        $daysPassed = now()->day;
        $daysRemaining = $daysInMonth - $daysPassed;
        
        $expectedSpendingByNow = ($currentBudget->amount / $daysInMonth) * $daysPassed;
        $actualSpending = $currentBudget->total_spent;
        $variance = $actualSpending - $expectedSpendingByNow;
        
        $dailyBudget = $currentBudget->amount / $daysInMonth;
        $remainingDailyBudget = $daysRemaining > 0 
            ? $currentBudget->remaining / $daysRemaining 
            : 0;

        return response()->json([
            'has_budget' => true,
            'budget' => [
                'amount' => $currentBudget->amount,
                'spent' => $currentBudget->total_spent,
                'remaining' => $currentBudget->remaining,
                'percentage_used' => round($currentBudget->percentage_used, 1),
            ],
            'insights' => [
                'days_in_month' => $daysInMonth,
                'days_passed' => $daysPassed,
                'days_remaining' => $daysRemaining,
                'expected_spending' => round($expectedSpendingByNow, 2),
                'actual_spending' => round($actualSpending, 2),
                'variance' => round($variance, 2),
                'variance_status' => $variance > 0 ? 'overspending' : 'underspending',
                'daily_budget_original' => round($dailyBudget, 2),
                'daily_budget_remaining' => round($remainingDailyBudget, 2),
            ],
            'recommendations' => $this->getRecommendations($currentBudget, $variance, $remainingDailyBudget, $dailyBudget),
        ]);
    }

    /**
     * Get personalized recommendations.
     */
    protected function getRecommendations(Budget $budget, float $variance, float $remainingDaily, float $originalDaily): array
    {
        $recommendations = [];

        if ($budget->isExceeded()) {
            $recommendations[] = [
                'type' => 'danger',
                'icon' => '⚠️',
                'message' => 'Budget exceeded! Review your expenses and consider cutting non-essential spending.',
            ];
        } elseif ($budget->isNearLimit()) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '⚡',
                'message' => 'You\'re near your budget limit. Be cautious with remaining expenses.',
            ];
        } else {
            $recommendations[] = [
                'type' => 'success',
                'icon' => '✅',
                'message' => 'Great job! You\'re within budget.',
            ];
        }

        if ($variance > 0) {
            $recommendations[] = [
                'type' => 'info',
                'icon' => '📊',
                'message' => sprintf('You\'re spending $%.2f more than expected for this time of month.', abs($variance)),
            ];
        } else {
            $recommendations[] = [
                'type' => 'success',
                'icon' => '💰',
                'message' => sprintf('You\'re spending $%.2f less than expected! Keep it up!', abs($variance)),
            ];
        }

        if ($remainingDaily < $originalDaily * 0.5) {
            $recommendations[] = [
                'type' => 'warning',
                'icon' => '🎯',
                'message' => sprintf('To stay within budget, limit daily spending to $%.2f for remaining days.', $remainingDaily),
            ];
        }

        return $recommendations;
    }
}
