<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Carbon\Carbon;

class TaskTemplateController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display template library
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $search = $request->query('search');

        $query = TaskTemplate::accessibleBy(Auth::id())
            ->with('user')
            ->orderBy('use_count', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->paginate(12);

        $categories = [
            'work' => '💼 Work',
            'personal' => '🏠 Personal',
            'health' => '💪 Health',
            'shopping' => '🛒 Shopping',
            'meeting' => '🤝 Meeting',
            'routine' => '🔄 Routine',
            'other' => '📋 Other',
        ];

        return view('templates.index', compact('templates', 'categories', 'category', 'search'));
    }

    /**
     * Show template creation form
     */
    public function create()
    {
        $categories = [
            'work' => '💼 Work',
            'personal' => '🏠 Personal',
            'health' => '💪 Health',
            'shopping' => '🛒 Shopping',
            'meeting' => '🤝 Meeting',
            'routine' => '🔄 Routine',
            'other' => '📋 Other',
        ];

        return view('templates.create', compact('categories'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:work,personal,health,shopping,meeting,routine,other',
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.priority' => 'required|in:low,medium,high,urgent',
            'tasks.*.due_offset' => 'nullable|integer', // Days from now
            'is_public' => 'boolean',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_public'] = $request->has('is_public');

        $template = TaskTemplate::create($validated);

        return redirect()->route('templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Show template details
     */
    public function show(TaskTemplate $template)
    {
        // Check access
        if ($template->user_id !== Auth::id() && !$template->is_public) {
            abort(403, 'You do not have access to this template.');
        }

        return view('templates.show', compact('template'));
    }

    /**
     * Show edit form
     */
    public function edit(TaskTemplate $template)
    {
        // Only owner can edit
        if ($template->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own templates.');
        }

        $categories = [
            'work' => '💼 Work',
            'personal' => '🏠 Personal',
            'health' => '💪 Health',
            'shopping' => '🛒 Shopping',
            'meeting' => '🤝 Meeting',
            'routine' => '🔄 Routine',
            'other' => '📋 Other',
        ];

        return view('templates.edit', compact('template', 'categories'));
    }

    /**
     * Update template
     */
    public function update(Request $request, TaskTemplate $template)
    {
        // Only owner can update
        if ($template->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own templates.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:work,personal,health,shopping,meeting,routine,other',
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.description' => 'nullable|string',
            'tasks.*.priority' => 'required|in:low,medium,high,urgent',
            'tasks.*.due_offset' => 'nullable|integer',
            'is_public' => 'boolean',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:50',
        ]);

        $validated['is_public'] = $request->has('is_public');

        $template->update($validated);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Delete template
     */
    public function destroy(TaskTemplate $template)
    {
        // Only owner can delete
        if ($template->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own templates.');
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully!');
    }

    /**
     * Apply template - create tasks from template
     */
    public function apply(Request $request, TaskTemplate $template)
    {
        // Check access
        if ($template->user_id !== Auth::id() && !$template->is_public) {
            abort(403, 'You do not have access to this template.');
        }

        $validated = $request->validate([
            'apply_date' => 'nullable|date',
        ]);

        $baseDate = $validated['apply_date'] ?? now();
        $createdTasks = [];

        foreach ($template->tasks as $taskData) {
            $taskAttributes = [
                'user_id' => Auth::id(),
                'title' => $this->substituteVariables($taskData['title']),
                'description' => isset($taskData['description']) 
                    ? $this->substituteVariables($taskData['description']) 
                    : null,
                'priority' => $taskData['priority'],
                'status' => 'pending',
            ];

            // Calculate due date based on offset
            if (isset($taskData['due_offset']) && $taskData['due_offset'] !== null) {
                $taskAttributes['due_date'] = Carbon::parse($baseDate)
                    ->addDays($taskData['due_offset']);
            }

            $task = Task::create($taskAttributes);
            $createdTasks[] = $task;
        }

        // Increment template use count
        $template->incrementUseCount();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($createdTasks) . ' tasks created from template',
                'tasks' => $createdTasks,
            ]);
        }

        return redirect()->route('tasks.index')
            ->with('success', count($createdTasks) . ' tasks created from template!');
    }

    /**
     * Get suggested templates based on context
     */
    public function suggestions(Request $request)
    {
        $hour = now()->hour;
        $dayOfWeek = now()->dayOfWeek;

        $suggestions = collect();

        // Morning suggestions (5 AM - 11 AM)
        if ($hour >= 5 && $hour < 12) {
            $suggestions = TaskTemplate::accessibleBy(Auth::id())
                ->byCategory('routine')
                ->where('name', 'like', '%morning%')
                ->popular(3)
                ->get();
        }
        // Afternoon suggestions (12 PM - 5 PM)
        elseif ($hour >= 12 && $hour < 17) {
            $suggestions = TaskTemplate::accessibleBy(Auth::id())
                ->byCategory('work')
                ->popular(3)
                ->get();
        }
        // Evening suggestions (5 PM - 9 PM)
        elseif ($hour >= 17 && $hour < 21) {
            $suggestions = TaskTemplate::accessibleBy(Auth::id())
                ->byCategory('personal')
                ->popular(3)
                ->get();
        }

        // Monday suggestions
        if ($dayOfWeek === 1) {
            $mondaySuggestions = TaskTemplate::accessibleBy(Auth::id())
                ->where('name', 'like', '%week%')
                ->popular(2)
                ->get();
            $suggestions = $suggestions->merge($mondaySuggestions);
        }

        return response()->json([
            'suggestions' => $suggestions->unique('id')->take(5)->values(),
        ]);
    }

    /**
     * Substitute template variables
     */
    private function substituteVariables(string $text): string
    {
        $now = now();

        $replacements = [
            '{date}' => $now->format('Y-m-d'),
            '{time}' => $now->format('H:i'),
            '{week}' => $now->format('W'),
            '{month}' => $now->format('F'),
            '{day}' => $now->format('l'),
            '{year}' => $now->format('Y'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }
}
