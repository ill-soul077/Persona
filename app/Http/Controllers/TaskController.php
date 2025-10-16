<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TaskController extends BaseController
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $view = $request->get('view', 'list');
        $priority = $request->get('priority');
        $tag = $request->get('tag');

        $query = Task::where('user_id', Auth::id())
            ->with(['history' => function($q) {
                $q->latest()->limit(5);
            }]);

        // Apply filters
        switch ($filter) {
            case 'today':
                $query->whereDate('due_date', today());
                break;
            case 'week':
                $query->whereBetween('due_date', [today(), today()->addDays(7)]);
                break;
            case 'overdue':
                $query->where('due_date', '<', now())->where('status', '!=', 'completed');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($tag) {
            $query->whereJsonContains('tags', $tag);
        }

        $tasks = $query->orderBy('due_date', 'asc')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->paginate(20);

        // Get stats for the view
        $stats = [
            'total' => Task::where('user_id', Auth::id())->count(),
            'today' => Task::where('user_id', Auth::id())->whereDate('due_date', today())->count(),
            'week' => Task::where('user_id', Auth::id())->whereBetween('due_date', [today(), today()->addDays(7)])->count(),
            'overdue' => Task::where('user_id', Auth::id())->where('due_date', '<', now())->where('status', '!=', 'completed')->count(),
            'completed' => Task::where('user_id', Auth::id())->where('status', 'completed')->count(),
        ];

        // Get all unique tags for filter
        $allTags = Task::where('user_id', Auth::id())
            ->whereNotNull('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        if ($view === 'calendar') {
            return view('tasks.calendar', compact('tasks', 'stats', 'allTags', 'filter'));
        }

        // Debug: Log what we're passing to the view
        Log::info('Tasks Controller Index', [
            'tasks_count' => $tasks->count(),
            'first_task' => $tasks->first() ? $tasks->first()->toArray() : null,
            'stats' => $stats,
            'filter' => $filter
        ]);

        return view('tasks.index', compact('tasks', 'stats', 'allTags', 'filter', 'priority', 'tag'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'tags' => 'nullable|string',
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        DB::beginTransaction();
        try {
            // Parse due date
            $dueDateTime = null;
            if ($validated['due_date']) {
                $dueDateTime = Carbon::parse($validated['due_date']);
            }

            // Parse tags
            $tags = null;
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $tags = array_filter($tags); // Remove empty tags
            }

            // Create task
            $task = Task::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $dueDateTime,
                'priority' => $validated['priority'],
                'recurrence_type' => 'none', // Default for simple form
                'recurrence_interval' => 1,
                'tags' => $tags,
                'status' => $validated['status'] ?? 'pending',
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task created successfully!',
                    'task' => $task
                ]);
            }

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create task. Please try again.'
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to create task. Please try again.');
        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        $task->load(['history' => function($q) {
            $q->latest();
        }]);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        return view('tasks.create', compact('task'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'tags' => 'nullable|string',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
        ]);

        DB::beginTransaction();
        try {
            // Parse due date
            $dueDateTime = null;
            if ($validated['due_date']) {
                $dueDateTime = Carbon::parse($validated['due_date']);
            }

            // Parse tags
            $tags = null;
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $validated['tags']));
                $tags = array_filter($tags); // Remove empty tags
            }

            // Update task
            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $dueDateTime,
                'priority' => $validated['priority'],
                'tags' => $tags,
                'status' => $validated['status'],
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task updated successfully!',
                    'task' => $task->fresh()
                ]);
            }

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task update failed', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update task. Please try again.'
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Failed to update task. Please try again.');
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        try {
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Task deletion failed', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task. Please try again.'
            ], 422);
        }
    }

    /**
     * Toggle task status (complete/pending)
     */
    public function toggleStatus(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        try {
            $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
            
            $task->update([
                'status' => $newStatus,
                'completed_at' => $newStatus === 'completed' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated!',
                'status' => $newStatus,
                'task' => $task->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Task status toggle failed', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status.'
            ], 422);
        }
    }
}