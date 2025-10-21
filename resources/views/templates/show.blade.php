@extends('layouts.app-master')

@section('title', $template->name)
@section('page-icon', $template->icon ?? $template->getCategoryIcon())
@section('page-title', $template->name)

@section('content')
<div class="animate-fade-in max-w-4xl mx-auto">
    <!-- Template Header -->
    <div class="glass-card rounded-xl p-8 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-3xl"
                     style="background: {{ $template->color ?? 'rgba(147, 51, 234, 0.2)' }}">
                    {{ $template->icon ?? $template->getCategoryIcon() }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $template->name }}</h1>
                    <div class="flex items-center space-x-3 mt-2">
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-{{ $template->getCategoryColor() }}-500/20 text-{{ $template->getCategoryColor() }}-400">
                            {{ $template->getCategoryIcon() }} {{ ucfirst($template->category) }}
                        </span>
                        @if($template->is_public)
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                Public Template
                            </span>
                        @endif
                        <span class="text-sm text-gray-400">
                            Used {{ $template->use_count }} times
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
                @if($template->user_id === Auth::id())
                    <a href="{{ route('templates.edit', $template) }}" 
                       class="px-4 py-2 rounded-lg font-medium bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 transition flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit</span>
                    </a>
                    <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-lg font-medium bg-red-500/20 hover:bg-red-500/30 text-red-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if($template->description)
            <p class="text-gray-300 text-lg">{{ $template->description }}</p>
        @endif
    </div>

    <!-- Template Tasks -->
    <div class="glass-card rounded-xl p-8 mb-6">
        <h2 class="text-2xl font-bold text-white mb-6">Template Tasks ({{ count($template->tasks) }})</h2>
        
        <div class="space-y-4">
            @foreach($template->tasks as $index => $task)
                <div class="p-6 bg-white/5 rounded-lg border border-white/10">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-gray-400 font-medium">#{{ $index + 1 }}</span>
                                <h3 class="text-lg font-semibold text-white">{{ $task['title'] }}</h3>
                            </div>
                            
                            @if(isset($task['description']) && $task['description'])
                                <p class="text-gray-300 mb-3">{{ $task['description'] }}</p>
                            @endif

                            <div class="flex items-center space-x-4 text-sm">
                                <!-- Priority Badge -->
                                <span class="px-3 py-1 rounded-full font-medium
                                    @if($task['priority'] === 'urgent') bg-red-500/20 text-red-400
                                    @elseif($task['priority'] === 'high') bg-orange-500/20 text-orange-400
                                    @elseif($task['priority'] === 'medium') bg-yellow-500/20 text-yellow-400
                                    @else bg-blue-500/20 text-blue-400
                                    @endif">
                                    {{ ucfirst($task['priority']) }} Priority
                                </span>

                                <!-- Due Date Offset -->
                                @if(isset($task['due_offset']) && $task['due_offset'] !== null)
                                    <span class="text-gray-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Due in {{ $task['due_offset'] }} {{ Str::plural('day', $task['due_offset']) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Apply Template -->
    <div class="glass-card rounded-xl p-8">
        <h2 class="text-2xl font-bold text-white mb-4">Apply This Template</h2>
        <p class="text-gray-300 mb-6">Create {{ count($template->tasks) }} tasks from this template.</p>

        <form action="{{ route('templates.apply', $template) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Apply Date (Optional)</label>
                <input type="date" 
                       name="apply_date" 
                       value="{{ now()->format('Y-m-d') }}"
                       class="w-full md:w-auto px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1">Due dates will be calculated from this date</p>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="glass-button text-white px-8 py-3 rounded-xl font-bold">
                    Apply Template Now
                </button>
                <a href="{{ route('templates.index') }}" 
                   class="px-6 py-3 rounded-xl font-medium bg-white/5 hover:bg-white/10 text-white transition">
                    Back to Library
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
