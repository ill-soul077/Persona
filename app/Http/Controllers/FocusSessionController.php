<?php

namespace App\Http\Controllers;

use App\Models\FocusSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;

class FocusSessionController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the focus mode interface
     */
    public function index(Request $request)
    {
        $taskId = $request->query('task_id');
        $task = null;

        if ($taskId) {
            $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        }

        // Get user's tasks for selection
        $tasks = Task::where('user_id', Auth::id())
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'asc')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->get();

        return view('focus.index', compact('tasks', 'task'));
    }

    /**
     * Start a new focus session
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'session_type' => 'required|in:work,short_break,long_break',
            'duration_minutes' => 'required|integer|min:1|max:60',
            'pomodoro_count' => 'required|integer|min:0|max:4',
        ]);

        // Verify task ownership if task_id provided
        if ($validated['task_id']) {
            $task = Task::where('user_id', Auth::id())->findOrFail($validated['task_id']);
        }

        $session = FocusSession::create([
            'user_id' => Auth::id(),
            'task_id' => $validated['task_id'] ?? null,
            'session_type' => $validated['session_type'],
            'duration_minutes' => $validated['duration_minutes'],
            'started_at' => now(),
            'pomodoro_count' => $validated['pomodoro_count'],
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
            'message' => 'Focus session started!'
        ]);
    }

    /**
     * Complete a focus session
     */
    public function complete(Request $request, FocusSession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'actual_minutes' => 'required|integer|min:0',
            'interrupted' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session->update([
            'completed_at' => now(),
            'actual_minutes' => $validated['actual_minutes'],
            'interrupted' => $validated['interrupted'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'session' => $session->fresh(),
            'message' => 'Session completed!'
        ]);
    }

    /**
     * Get focus analytics
     */
    public function analytics(Request $request)
    {
        $period = $request->query('period', 'week'); // week, month, year
        
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };

        $sessions = FocusSession::forUser(Auth::id())
            ->completed()
            ->where('started_at', '>=', $startDate)
            ->with('task')
            ->get();

        $stats = [
            'total_sessions' => $sessions->count(),
            'work_sessions' => $sessions->where('session_type', 'work')->count(),
            'total_focus_minutes' => $sessions->where('session_type', 'work')->sum('actual_minutes'),
            'completed_pomodoros' => $sessions->where('session_type', 'work')->where('interrupted', false)->count(),
            'interrupted_sessions' => $sessions->where('interrupted', true)->count(),
            'average_session_length' => $sessions->where('session_type', 'work')->avg('actual_minutes'),
            'tasks_focused' => $sessions->where('session_type', 'work')->pluck('task_id')->unique()->count(),
        ];

        // Daily breakdown
        $dailyStats = $sessions->groupBy(function($session) {
            return $session->started_at->format('Y-m-d');
        })->map(function($daySessions) {
            return [
                'sessions' => $daySessions->count(),
                'focus_minutes' => $daySessions->where('session_type', 'work')->sum('actual_minutes'),
            ];
        });

        // Task breakdown
        $taskStats = $sessions->where('session_type', 'work')
            ->groupBy('task_id')
            ->map(function($taskSessions) {
                $task = $taskSessions->first()->task;
                return [
                    'task_id' => $task?->id,
                    'task_title' => $task?->title ?? 'No Task',
                    'sessions' => $taskSessions->count(),
                    'total_minutes' => $taskSessions->sum('actual_minutes'),
                ];
            })
            ->sortByDesc('total_minutes')
            ->take(10)
            ->values();

        return view('focus.analytics', compact('stats', 'dailyStats', 'taskStats', 'period'));
    }

    /**
     * Get session history
     */
    public function history(Request $request)
    {
        $sessions = FocusSession::forUser(Auth::id())
            ->with('task')
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return response()->json([
            'sessions' => $sessions
        ]);
    }
}
