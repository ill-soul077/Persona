@extends('layouts.app-master')

@section('title', 'Task Tracker')
@section('page-icon', '📋')
@section('page-title', 'Task Tracker')



@section('content')
<!-- Task Tracker Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Task Tracker</h1>
            <p class="text-gray-300 mt-2">Manage your tasks and stay productive</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button onclick="showQuickAddModal()" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Quick Add</span>
            </button>
            <a href="{{ route('tasks.create') }}" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Task</span>
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6 border-l-4 border-blue-500 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-3xl font-bold text-white mb-2">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-300">Total Tasks</div>
            </div>
            <div class="text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-xl p-6 border-l-4 border-red-500 animate-bounce-in" style="animation-delay: 0.1s;">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-3xl font-bold text-white mb-2">{{ $stats['today'] }}</div>
                <div class="text-sm text-gray-300">Due Today</div>
            </div>
            <div class="text-red-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-xl p-6 border-l-4 border-yellow-500 animate-bounce-in" style="animation-delay: 0.2s;">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-3xl font-bold text-white mb-2">{{ $stats['week'] }}</div>
                <div class="text-sm text-gray-300">This Week</div>
            </div>
            <div class="text-yellow-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-xl p-6 border-l-4 border-orange-500 animate-bounce-in" style="animation-delay: 0.3s;">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-3xl font-bold text-white mb-2">{{ $stats['overdue'] }}</div>
                <div class="text-sm text-gray-300">Overdue</div>
            </div>
            <div class="text-orange-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-xl p-6 border-l-4 border-green-500 animate-bounce-in" style="animation-delay: 0.4s;">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-3xl font-bold text-white mb-2">{{ $stats['completed'] }}</div>
                <div class="text-sm text-gray-300">Completed</div>
            </div>
            <div class="text-green-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters and View Toggles -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-wrap gap-4 items-center justify-between">
        <!-- Filter Buttons -->
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tasks.index', ['filter' => 'all']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 {{ $filter === 'all' ? 'bg-gradient-to-r from-purple-500 to-blue-500 text-white shadow-lg' : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white' }}">
                All Tasks
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'today']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 {{ $filter === 'today' ? 'bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg' : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white' }}">
                Due Today
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'week']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 {{ $filter === 'week' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg' : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white' }}">
                This Week
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'overdue']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 {{ $filter === 'overdue' ? 'bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-lg' : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white' }}">
                Overdue
            </a>
            <a href="{{ route('tasks.index', ['filter' => 'completed']) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 {{ $filter === 'completed' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg' : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white' }}">
                Completed
            </a>
        </div>

        <!-- View Toggle -->
        <div class="flex gap-3">
            <a href="{{ route('tasks.index', ['view' => 'list'] + request()->query()) }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>List</span>
            </a>
            <a href="{{ route('tasks.calendar') }}" 
               class="px-4 py-2 rounded-xl font-medium transition-all duration-300 bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Calendar</span>
            </a>
        </div>
    </div>
</div>

<!-- Tasks List -->
<div class="glass-card rounded-xl overflow-hidden animate-fade-in">
    @forelse($tasks as $task)
        <div class="p-6 border-b border-white/10 hover:bg-white/5 transition-all duration-300 {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
            <div class="flex items-start gap-4">
                <!-- Checkbox -->
                <div class="flex-shrink-0 mt-1">
                    <input type="checkbox" 
                           {{ $task->status === 'completed' ? 'checked' : '' }}
                           onchange="toggleTaskStatus({{ $task->id }})"
                           class="w-5 h-5 text-blue-500 bg-white/10 border-white/30 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer transition-all">
                </div>

                <!-- Task Content -->
                <div class="flex-grow">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="text-lg font-semibold text-white hover:text-blue-400 transition-colors {{ $task->status === 'completed' ? 'line-through opacity-75' : '' }}">
                                {{ $task->title }}
                            </a>
                            @if($task->description)
                                <p class="mt-2 text-sm text-gray-300">{{ Str::limit($task->description, 100) }}</p>
                            @endif
                            
                            <!-- Meta Info -->
                            <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                @if($task->due_date)
                                    <span class="flex items-center gap-1.5 px-3 py-1 rounded-full {{ $task->is_overdue ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30' }}">
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
                                <span class="px-3 py-1 rounded-full text-xs font-medium border
                                    {{ $task->priority === 'high' ? 'bg-red-500/20 text-red-300 border-red-500/30' : '' }}
                                    {{ $task->priority === 'medium' ? 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30' : '' }}
                                    {{ $task->priority === 'low' ? 'bg-green-500/20 text-green-300 border-green-500/30' : '' }}">
                                    {{ ucfirst($task->priority) }} Priority
                                </span>

                                @if($task->recurrence_type !== 'none')
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full text-xs font-medium">
                                        🔄 {{ ucfirst($task->recurrence_type) }}
                                    </span>
                                @endif

                                @if($task->tags)
                                    @foreach($task->tags as $tag)
                                        <span class="px-3 py-1 bg-gray-500/20 text-gray-300 border border-gray-500/30 rounded-full text-xs">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <a href="{{ route('tasks.edit', $task) }}" class="text-blue-400 hover:text-blue-300 transition-colors p-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors p-2 rounded-lg hover:bg-white/10">
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
        <div class="p-16 text-center">
            <div class="mx-auto w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No tasks found</h3>
            <p class="text-gray-300 mb-8 max-w-md mx-auto">Ready to get productive? Create your first task and start managing your workflow efficiently.</p>
            <a href="{{ route('tasks.create') }}" class="glass-button text-white px-6 py-3 rounded-xl font-medium inline-flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Your First Task</span>
            </a>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($tasks->hasPages())
    <div class="mt-8 flex justify-center">
        <div class="glass-card rounded-xl px-6 py-3">
            {{ $tasks->links() }}
        </div>
    </div>
@endif
@endsection

@section('modals')
<!-- Quick Add Modal -->
<div id="quickAddModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="glass-card rounded-xl shadow-2xl p-6 w-full max-w-md mx-4 animate-bounce-in">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center space-x-2">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Quick Add Task</span>
        </h3>
        <form id="quickAddForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-200 mb-2">Task Title</label>
                <input type="text" name="title" required class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-200 mb-2">Due Date</label>
                <input type="datetime-local" name="due_date" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-200 mb-2">Priority</label>
                <select name="priority" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="low" class="bg-gray-800">Low Priority</option>
                    <option value="medium" selected class="bg-gray-800">Medium Priority</option>
                    <option value="high" class="bg-gray-800">High Priority</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 glass-button text-white px-4 py-3 rounded-xl font-medium transition-all">
                    Add Task
                </button>
                <button type="button" onclick="hideQuickAddModal()" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-medium transition-all border border-white/20">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Modal Functions
    function showQuickAddModal() {
        document.getElementById('quickAddModal').classList.remove('hidden');
    }

    function hideQuickAddModal() {
        document.getElementById('quickAddModal').classList.add('hidden');
        document.getElementById('quickAddForm').reset();
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideQuickAddModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('quickAddModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideQuickAddModal();
        }
    });

    // Quick Add Form Submission
    document.getElementById('quickAddForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const submitButton = e.target.querySelector('button[type="submit"]');
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding...';
        
        try {
            const response = await fetch('{{ route("tasks.quick.add") }}', {
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
                // Success animation
                submitButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Added!';
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                throw new Error('Failed to add task');
            }
        } catch (error) {
            console.error('Error:', error);
            submitButton.innerHTML = 'Add Task';
            submitButton.disabled = false;
            
            // Show error notification
            showNotification('Failed to add task. Please try again.', 'error');
        }
    });

    // Toggle Task Status
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
                showNotification('Task updated successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } else {
                throw new Error('Failed to update task');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Failed to update task status.', 'error');
        }
    }

    // Notification System
    function showNotification(message, type) {
        const bgColor = type === 'success' ? 'bg-green-500/20 border-green-500/30 text-green-300' : 'bg-red-500/20 border-red-500/30 text-red-300';
        const icon = type === 'success' ? 
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : 
            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${bgColor} px-4 py-3 rounded-xl backdrop-blur-sm z-50 animate-bounce-in`;
        notification.innerHTML = `<div class="flex items-center space-x-2">${icon}<span>${message}</span></div>`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Initialize animations on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add staggered animation delays to task items
        const taskItems = document.querySelectorAll('.glass-card .p-6');
        taskItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
            item.classList.add('animate-fade-in');
        });
    });
</script>
@endsection