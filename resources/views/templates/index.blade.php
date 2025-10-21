@extends('layouts.app-master')

@section('title', 'Task Templates')
@section('page-icon', '📝')
@section('page-title', 'Task Templates')

@section('content')
<div class="animate-fade-in">
    <!-- Header with Actions -->
    <div class="glass-card rounded-xl p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Template Library</h1>
                <p class="text-gray-300 mt-2">Speed up task creation with pre-defined templates</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('templates.create') }}" class="glass-button text-white px-6 py-3 rounded-xl font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Template</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Category Filters & Search -->
    <div class="glass-card rounded-xl p-6 mb-6 animate-slide-up">
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <!-- Category Pills -->
            <div class="flex flex-wrap gap-2 flex-1">
                <a href="{{ route('templates.index') }}" 
                   class="px-4 py-2 rounded-lg font-medium transition {{ !$category ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    All
                </a>
                @foreach($categories as $key => $label)
                    <a href="{{ route('templates.index', ['category' => $key]) }}" 
                       class="px-4 py-2 rounded-lg font-medium transition {{ $category === $key ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Search -->
            <form action="{{ route('templates.index') }}" method="GET" class="flex gap-2">
                @if($category)
                    <input type="hidden" name="category" value="{{ $category }}">
                @endif
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}"
                       placeholder="Search templates..." 
                       class="px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <button type="submit" class="glass-button text-white px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-bounce-in">
        @forelse($templates as $template)
            <div class="glass-card rounded-xl p-6 hover:scale-105 transition-all cursor-pointer" 
                 x-data="{ showActions: false }"
                 @mouseenter="showActions = true"
                 @mouseleave="showActions = false">
                <!-- Template Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl"
                             style="background: {{ $template->color ?? 'rgba(147, 51, 234, 0.2)' }}">
                            {{ $template->icon ?? $template->getCategoryIcon() }}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white">{{ $template->name }}</h3>
                            <p class="text-xs text-gray-400">
                                <span class="inline-flex items-center">
                                    {{ $template->getCategoryIcon() }} {{ ucfirst($template->category) }}
                                    @if($template->is_public)
                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions (on hover) -->
                    <div x-show="showActions" 
                         x-transition
                         class="flex space-x-1">
                        @if($template->user_id === Auth::id())
                            <a href="{{ route('templates.edit', $template) }}" 
                               class="p-2 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                @if($template->description)
                    <p class="text-gray-300 text-sm mb-4">{{ Str::limit($template->description, 100) }}</p>
                @endif

                <!-- Task Count -->
                <div class="flex items-center text-sm text-gray-400 mb-4">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    {{ count($template->tasks) }} tasks
                    <span class="mx-2">•</span>
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Used {{ $template->use_count }} times
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <form action="{{ route('templates.apply', $template) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full glass-button text-white px-4 py-2 rounded-lg font-medium text-sm">
                            Apply Template
                        </button>
                    </form>
                    <a href="{{ route('templates.show', $template) }}" 
                       class="px-4 py-2 rounded-lg font-medium text-sm bg-white/5 hover:bg-white/10 text-white transition">
                        Preview
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-xl p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No templates found</h3>
                <p class="text-gray-400 mb-6">{{ $search ? 'Try a different search term or filter' : 'Create your first template to get started' }}</p>
                <a href="{{ route('templates.create') }}" class="glass-button text-white px-6 py-3 rounded-xl font-medium inline-block">
                    Create Template
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
