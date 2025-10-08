@extends('layouts.app')

@section('title', 'Task Tracker')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-indigo-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">📋 Task Tracker</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your tasks efficiently</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="showQuickAddModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Quick Add
                    </button>
                    <a href="{{ route('tasks.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Task
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-600">Total Tasks</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['today'] }}</div>
                <div class="text-sm text-gray-600">Due Today</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['week'] }}</div>
                <div class="text-sm text-gray-600">This Week</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['overdue'] }}</div>
                <div class="text-sm text-gray-600">Overdue</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</div>
                <div class="text-sm text-gray-600">Completed</div>
            </div>
        </div>

        <!-- Filters and View Toggles -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <!-- Filter Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('tasks.index', ['filter' => 'all']) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All
                    </a>
                    <a href="{{ route('tasks.index', ['filter' => 'today']) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'today' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Today
                    </a>
                    <a href="{{ route('tasks.index', ['filter' => 'week']) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'week' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        This Week
                    </a>
                    <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'overdue' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Overdue
                    </a>
                    <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'completed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Completed
                    </a>
                </div>

                <!-- View Toggle -->
                <div class="flex gap-2">
                    <a href="{{ route('tasks.index', ['view' => 'list'] + request()->query()) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition bg-gray-100 text-gray-700 hover:bg-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </a>
                    <a href="{{ route('tasks.index', ['view' => 'calendar'] + request()->query()) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition bg-gray-100 text-gray-700 hover:bg-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            @forelse($tasks as $task)
                <div class="p-6 border-b border-gray-200 hover:bg-gray-50 transition {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
                    <div class="flex items-start gap-4">
                        <!-- Checkbox -->
                        <div class="flex-shrink-0 mt-1">
                            <input type="checkbox" 
                                   {{ $task->status === 'completed' ? 'checked' : '' }}
                                   onchange="toggleTaskStatus({{ $task->id }})"
                                   class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500 cursor-pointer">
                        </div>

                        <!-- Task Content -->
                        <div class="flex-grow">
                            <div class="flex items-start justify-between">
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="text-lg font-semibold text-gray-900 hover:text-purple-600 {{ $task->status === 'completed' ? 'line-through' : '' }}">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->description)
                                        <p class="mt-1 text-sm text-gray-600">{{ Str::limit($task->description, 100) }}</p>
                                    @endif
                                    
                                    <!-- Meta Info -->
                                    <div class="mt-2 flex flex-wrap gap-3 text-sm">
                                        @if($task->due_date)
                                            <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $task->due_date->format('M d, Y') }}
                                                @if($task->due_date->format('H:i') !== '00:00')
                                                    at {{ $task->due_date->format('g:i A') }}
                                                @endif
                                            </span>
                                        @endif

                                        <!-- Priority Badge -->
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $task->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}">
                                            {{ ucfirst($task->priority) }} Priority
                                        </span>

                                        @if($task->recurrence_type !== 'none')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                🔄 {{ ucfirst($task->recurrence_type) }}
                                            </span>
                                        @endif

                                        @if($task->tags)
                                            @foreach($task->tags as $tag)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                                                    #{{ $tag }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-gray-600 hover:text-purple-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-600 hover:text-red-600 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No tasks found</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first task!</p>
                    <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Task
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($tasks->hasPages())
            <div class="mt-6">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Quick Add Modal -->
<div id="quickAddModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Add Task</h3>
        <form id="quickAddForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
                <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input type="datetime-local" name="due_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition">
                    Add Task
                </button>
                <button type="button" onclick="hideQuickAddModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showQuickAddModal() {
    document.getElementById('quickAddModal').classList.remove('hidden');
}

function hideQuickAddModal() {
    document.getElementById('quickAddModal').classList.add('hidden');
    document.getElementById('quickAddForm').reset();
}

document.getElementById('quickAddForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('{{ route('tasks.quick.add') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: formData.get('title'),
                due_date: formData.get('due_date'),
                priority: formData.get('priority'),
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to add task. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to add task. Please try again.');
    }
});

async function toggleTaskStatus(taskId) {
    try {
        const response = await fetch(`/tasks/${taskId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update task status.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update task status.');
    }
}
</script>
@endsection
