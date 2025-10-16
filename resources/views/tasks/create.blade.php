@extends('layouts.app-master')

@section('title', isset($task) ? 'Edit Task' : 'Create Task')
@section('page-icon', '📋')
@section('page-title', isset($task) ? 'Edit Task' : 'Create Task')

@section('action-buttons')
<a href="{{ route('tasks.index') }}" 
   class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    <span>Back to Tasks</span>
</a>
@endsection

@section('content')
<!-- Page Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">{{ isset($task) ? 'Edit Task' : 'Create New Task' }}</h1>
            <p class="text-gray-300 mt-2">{{ isset($task) ? 'Update task details' : 'Add a new task to your list' }}</p>
        </div>
    </div>
</div>

<!-- Task Form -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <form method="POST" 
          action="{{ isset($task) ? route('tasks.update', $task) : route('tasks.store') }}"
          x-data="taskForm({{ isset($task) ? json_encode($task) : 'null' }})"
          x-init="init()">
        @csrf
        @if(isset($task))
            @method('PUT')
        @endif

        <!-- Task Title -->
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-200 mb-2">Task Title *</label>
            <input type="text" 
                   name="title" 
                   id="title" 
                   x-model="form.title"
                   required
                   placeholder="Enter task title..."
                   class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('title') border-red-400 @enderror">
            @error('title')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Task Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-200 mb-2">Description</label>
            <textarea name="description" 
                      id="description" 
                      rows="4" 
                      x-model="form.description"
                      placeholder="Optional task description..."
                      class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('description') border-red-400 @enderror"></textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Priority -->
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-200 mb-2">Priority</label>
                <select name="priority" 
                        id="priority" 
                        x-model="form.priority"
                        class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('priority') border-red-400 @enderror">
                    <option value="low" class="bg-gray-800">Low Priority</option>
                    <option value="medium" class="bg-gray-800">Medium Priority</option>
                    <option value="high" class="bg-gray-800">High Priority</option>
                    <option value="urgent" class="bg-gray-800">Urgent</option>
                </select>
                @error('priority')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Due Date -->
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-200 mb-2">Due Date</label>
                <input type="date" 
                       name="due_date" 
                       id="due_date" 
                       x-model="form.due_date"
                       :min="new Date().toISOString().split('T')[0]"
                       class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('due_date') border-red-400 @enderror">
                @error('due_date')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Tags -->
        <div class="mt-6">
            <label for="tags" class="block text-sm font-medium text-gray-200 mb-2">Tags</label>
            <input type="text" 
                   name="tags" 
                   id="tags" 
                   x-model="form.tags"
                   placeholder="e.g., work, personal, urgent (separate with commas)..."
                   class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('tags') border-red-400 @enderror">
            <p class="mt-1 text-xs text-gray-400">Separate multiple tags with commas</p>
            @error('tags')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Status (only for editing) -->
        @if(isset($task))
        <div class="mt-6">
            <label for="status" class="block text-sm font-medium text-gray-200 mb-2">Status</label>
            <select name="status" 
                    id="status" 
                    x-model="form.status"
                    class="block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('status') border-red-400 @enderror">
                <option value="pending" class="bg-gray-800">Pending</option>
                <option value="in_progress" class="bg-gray-800">In Progress</option>
                <option value="completed" class="bg-gray-800">Completed</option>
                <option value="cancelled" class="bg-gray-800">Cancelled</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-8 flex items-center justify-end space-x-3">
            <a href="{{ route('tasks.index') }}" 
               class="inline-flex items-center px-6 py-3 border border-white/20 text-sm font-medium rounded-xl text-gray-300 bg-white/10 hover:bg-white/20 transition-all backdrop-blur-sm">
                Cancel
            </a>
            
            <button type="submit" 
                    class="glass-button inline-flex items-center px-6 py-3 text-sm font-medium rounded-xl text-white transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ isset($task) ? 'Update Task' : 'Create Task' }}
            </button>
        </div>
    </form>
</div>
@endsection

@section('additional-scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

@section('scripts')
<script>
function taskForm(existing = null) {
    return {
        form: {
            title: existing?.title || '',
            description: existing?.description || '',
            priority: existing?.priority || 'medium',
            due_date: existing?.due_date || '',
            tags: existing?.tags ? existing.tags.join(', ') : '',
            status: existing?.status || 'pending'
        },
        
        init() {
            console.log('Task form initialized with:', this.form);
        }
    };
}
</script>
@endsection