<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskReminder;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TaskController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
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

        $query = Task::where('user_id', auth()->id())
            ->with(['history' => function($q) {
                $q->latest()->limit(5);
            }]);

        // Apply filters
        switch ($filter) {
            case 'today':
                $query->dueToday();
                break;
            case 'week':
                $query->dueInDays(7);
                break;
            case 'overdue':
                $query->overdue();
                break;
            case 'completed':
                $query->completed();
                break;
            case 'pending':
                $query->pending();
                break;
        }

        if ($priority) {
            $query->priority($priority);
        }

        if ($tag) {
            $query->withTag($tag);
        }

        $tasks = $query->orderBy('due_date', 'asc')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->paginate(20);

        // Get stats for the view
        $stats = [
            'total' => Task::where('user_id', auth()->id())->count(),
            'today' => Task::where('user_id', auth()->id())->dueToday()->count(),
            'week' => Task::where('user_id', auth()->id())->dueInDays(7)->count(),
            'overdue' => Task::where('user_id', auth()->id())->overdue()->count(),
            'completed' => Task::where('user_id', auth()->id())->completed()->count(),
        ];

        // Get all unique tags for filter
        $allTags = Task::where('user_id', auth()->id())
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
            'due_time' => 'nullable|date_format:H:i',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'recurrence_type' => ['required', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
            'recurrence_end_date' => 'nullable|date|after:due_date',
            'tags' => 'nullable|string',
            'set_reminder' => 'nullable|boolean',
            'reminder_time' => 'nullable|date_format:H:i',
        ]);

        DB::beginTransaction();
        try {
            // Combine date and time
            $dueDateTime = null;
            if ($validated['due_date']) {
                $dueDateTime = Carbon::parse($validated['due_date']);
                if ($request->filled('due_time')) {
                    $time = Carbon::parse($validated['due_time']);
                    $dueDateTime->setTime($time->hour, $time->minute);
                }
            }

            // Parse tags
            $tags = null;
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $validated['tags']));
            }

            // Create task
            $task = Task::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $dueDateTime,
                'priority' => $validated['priority'],
                'recurrence_type' => $validated['recurrence_type'],
                'recurrence_interval' => $validated['recurrence_interval'] ?? 1,
                'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
                'tags' => $tags,
                'status' => 'pending',
            ]);

            // Calculate next occurrence for recurring tasks
            if ($task->recurrence_type !== 'none' && $dueDateTime) {
                $task->next_occurrence = $task->calculateNextOccurrence();
                $task->save();
            }

            // Log history
            $task->logHistory('created', [
                'title' => $task->title,
                'due_date' => $task->due_date?->toIso8601String(),
            ]);

            // Create reminder if requested
            if ($request->boolean('set_reminder') && $dueDateTime) {
                $remindAt = $dueDateTime->copy();
                
                if ($request->filled('reminder_time')) {
                    $reminderTime = Carbon::parse($validated['reminder_time']);
                    $remindAt->setTime($reminderTime->hour, $reminderTime->minute);
                } else {
                    $remindAt->subHour(); // Default: 1 hour before
                }

                TaskReminder::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'remind_at' => $remindAt,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

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
        $this->authorize('view', $task);

        $task->load(['history' => function($q) {
            $q->latest();
        }, 'reminders']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'recurrence_type' => ['required', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
            'recurrence_interval' => 'nullable|integer|min:1|max:365',
            'recurrence_end_date' => 'nullable|date|after:due_date',
            'tags' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $task->toArray();

            // Combine date and time
            $dueDateTime = null;
            if ($validated['due_date']) {
                $dueDateTime = Carbon::parse($validated['due_date']);
                if ($request->filled('due_time')) {
                    $time = Carbon::parse($validated['due_time']);
                    $dueDateTime->setTime($time->hour, $time->minute);
                }
            }

            // Parse tags
            $tags = null;
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $validated['tags']));
            }

            // Update task
            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'due_date' => $dueDateTime,
                'priority' => $validated['priority'],
                'recurrence_type' => $validated['recurrence_type'],
                'recurrence_interval' => $validated['recurrence_interval'] ?? 1,
                'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
                'tags' => $tags,
            ]);

            // Calculate next occurrence for recurring tasks
            if ($task->recurrence_type !== 'none' && $dueDateTime) {
                $task->next_occurrence = $task->calculateNextOccurrence();
                $task->save();
            } else {
                $task->next_occurrence = null;
                $task->save();
            }

            // Log changes
            $changes = array_diff_assoc($task->toArray(), $oldData);
            $task->logHistory('updated', $changes);

            DB::commit();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Task update failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update task. Please try again.');
        }
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        try {
            $task->logHistory('deleted', ['deleted_at' => now()]);
            $task->delete();

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Task deletion failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete task. Please try again.');
        }
    }

    /**
     * Toggle task completion status.
     */
    public function toggleStatus(Task $task)
    {
        $this->authorize('update', $task);

        try {
            if ($task->status === 'completed') {
                $task->markAsPending();
                $message = 'Task marked as pending.';
            } else {
                $task->markAsCompleted();
                $message = 'Task marked as completed!';
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'task' => $task->fresh(),
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Task status toggle failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update task status.',
                ], 500);
            }

            return back()->with('error', 'Failed to update task status.');
        }
    }

    /**
     * Get tasks for calendar view (JSON feed).
     */
    public function calendarFeed(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Task::where('user_id', auth()->id());

        if ($start && $end) {
            $query->whereBetween('due_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ]);
        }

        $tasks = $query->get()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $task->due_date?->toIso8601String(),
                'end' => $task->due_date?->toIso8601String(),
                'url' => route('tasks.show', $task),
                'backgroundColor' => $this->getPriorityColor($task->priority),
                'borderColor' => $this->getPriorityColor($task->priority),
                'classNames' => $task->status === 'completed' ? ['completed-task'] : [],
                'extendedProps' => [
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'tags' => $task->tags,
                ],
            ];
        });

        return response()->json($tasks);
    }

    /**
     * Get priority color for calendar.
     */
    protected function getPriorityColor(string $priority): string
    {
        return match($priority) {
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#10b981',
            default => '#6b7280',
        };
    }

    /**
     * Quick add task (AJAX).
     */
    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        try {
            $task = Task::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'due_date' => $validated['due_date'] ?? null,
                'priority' => $validated['priority'] ?? 'medium',
                'status' => 'pending',
                'recurrence_type' => 'none',
            ]);

            $task->logHistory('created', ['title' => $task->title]);

            return response()->json([
                'success' => true,
                'message' => 'Task added successfully!',
                'task' => $task,
            ]);

        } catch (\Exception $e) {
            Log::error('Quick add task failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add task.',
            ], 500);
        }
    }
}
