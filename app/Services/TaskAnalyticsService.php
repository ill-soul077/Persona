<?php

namespace App\Services;

use App\Models\Task;
use App\Models\FocusSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskAnalyticsService
{
    /**
     * Get tasks completed per day trend
     */
    public function getCompletionTrend($startDate, $endDate, $userId)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $completedTasks = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        
        // Fill in missing dates with 0
        $result = [];
        $labels = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $dateKey = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            $result[] = (float) ($completedTasks[$dateKey] ?? 0);
            $current->addDay();
        }
        
        return [
            'labels' => $labels,
            'data' => $result
        ];
    }
    
    /**
     * Get priority distribution
     */
    public function getPriorityDistribution($userId, $filters = [])
    {
        $query = Task::where('user_id', $userId);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }
        
        $distribution = $query->select('priority', DB::raw('COUNT(*) as count'))
            ->groupBy('priority')
            ->get();
        
        $labels = [];
        $data = [];
        $colors = [
            'low' => 'rgba(107, 114, 128, 0.8)',
            'medium' => 'rgba(245, 158, 11, 0.8)',
            'high' => 'rgba(249, 115, 22, 0.8)',
            'urgent' => 'rgba(239, 68, 68, 0.8)'
        ];
        
        foreach ($distribution as $item) {
            $labels[] = ucfirst($item->priority);
            $data[] = (float) $item->count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_map(fn($label) => $colors[strtolower($label)] ?? 'rgba(139, 92, 246, 0.8)', $labels)
        ];
    }
    
    /**
     * Get productivity heatmap (tasks completed by hour)
     */
    public function getProductivityHeatmap($period, $userId)
    {
        $startDate = Carbon::now()->subDays($period);
        
        $hourlyData = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $startDate)
            ->select(DB::raw('HOUR(completed_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
        
        $labels = [];
        $data = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $data[] = (float) ($hourlyData[$hour] ?? 0);
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Get category breakdown
     */
    public function getCategoryBreakdown($userId, $filters = [])
    {
        $query = Task::where('user_id', $userId);
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        
        // Extract categories from tags JSON field
        $tasks = $query->get();
        $categoryCount = [];
        
        foreach ($tasks as $task) {
            if ($task->tags && is_array($task->tags)) {
                foreach ($task->tags as $tag) {
                    if (!isset($categoryCount[$tag])) {
                        $categoryCount[$tag] = 0;
                    }
                    $categoryCount[$tag]++;
                }
            } else {
                if (!isset($categoryCount['Uncategorized'])) {
                    $categoryCount['Uncategorized'] = 0;
                }
                $categoryCount['Uncategorized']++;
            }
        }
        
        arsort($categoryCount);
        
        return [
            'labels' => array_keys($categoryCount),
            'data' => array_values($categoryCount)
        ];
    }
    
    /**
     * Get overview metrics
     */
    public function getOverviewMetrics($userId)
    {
        // Tasks completed today
        $completedToday = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->count();
        
        // Weekly completion rate
        $weekStart = Carbon::now()->startOfWeek();
        $totalThisWeek = Task::where('user_id', $userId)
            ->whereBetween('created_at', [$weekStart, Carbon::now()])
            ->count();
        
        $completedThisWeek = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$weekStart, Carbon::now()])
            ->count();
        
        $weeklyRate = $totalThisWeek > 0 ? ($completedThisWeek / $totalThisWeek) * 100 : 0;
        
        // Current streak (consecutive days with at least 1 completed task)
        $streak = $this->calculateStreak($userId);
        
        // Average tasks per day (last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $completedLast30Days = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $thirtyDaysAgo)
            ->count();
        
        $avgPerDay = $completedLast30Days / 30;
        
        return [
            'completed_today' => $completedToday,
            'weekly_rate' => round($weeklyRate, 1),
            'current_streak' => $streak,
            'avg_per_day' => round($avgPerDay, 1)
        ];
    }
    
    /**
     * Calculate completion streak
     */
    private function calculateStreak($userId)
    {
        $streak = 0;
        $currentDate = Carbon::today();
        
        while (true) {
            $completed = Task::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $currentDate)
                ->exists();
            
            if (!$completed) {
                break;
            }
            
            $streak++;
            $currentDate->subDay();
            
            // Safety limit
            if ($streak > 365) {
                break;
            }
        }
        
        return $streak;
    }
    
    /**
     * Generate smart insights
     */
    public function generateSmartInsights($userId)
    {
        $insights = [];
        
        // Most productive day of week
        $mostProductiveDay = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', Carbon::now()->subDays(90))
            ->select(DB::raw('DAYNAME(completed_at) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderBy('count', 'desc')
            ->first();
        
        if ($mostProductiveDay) {
            $insights[] = [
                'icon' => '📅',
                'title' => 'Most Productive Day',
                'message' => "You're most productive on {$mostProductiveDay->day}s with an average of {$mostProductiveDay->count} tasks completed."
            ];
        }
        
        // Most productive hour
        $mostProductiveHour = Task::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('HOUR(completed_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();
        
        if ($mostProductiveHour) {
            $timeSlot = $this->getTimeSlot($mostProductiveHour->hour);
            $insights[] = [
                'icon' => '⏰',
                'title' => 'Peak Performance Time',
                'message' => "You complete most tasks during {$timeSlot} ({$mostProductiveHour->hour}:00)."
            ];
        }
        
        // Priority completion rate
        $urgentTotal = Task::where('user_id', $userId)
            ->where('priority', 'urgent')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        $urgentCompleted = Task::where('user_id', $userId)
            ->where('priority', 'urgent')
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        if ($urgentTotal > 0) {
            $urgentRate = ($urgentCompleted / $urgentTotal) * 100;
            $insights[] = [
                'icon' => '🎯',
                'title' => 'Urgent Task Performance',
                'message' => sprintf('High-priority tasks have a %.0f%% completion rate. %s', 
                    $urgentRate,
                    $urgentRate < 70 ? 'Consider breaking them into smaller tasks.' : 'Great job staying on top of priorities!')
            ];
        }
        
        return $insights;
    }
    
    /**
     * Get time slot name from hour
     */
    private function getTimeSlot($hour)
    {
        if ($hour >= 5 && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'afternoon';
        if ($hour >= 17 && $hour < 21) return 'evening';
        return 'night';
    }
}
