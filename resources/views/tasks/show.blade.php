@extends('layouts.app-master')

@section('title', 'Task Details')
@section('page-icon', '📝')
@section('page-title', 'Task Details')

@section('action-buttons')
<div class="flex space-x-3">
    @if($task->status !== 'completed')
    <a href="{{ route('focus.index', ['task_id' => $task->id]) }}" 
       class="bg-purple-600/80 hover:bg-purple-500 text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Focus on This Task</span>
    </a>
    @endif
    <a href="{{ route('tasks.index') }}" 
       class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to Tasks</span>
    </a>
</div>
@endsection

@section('content')
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $task->title }}</h1>
            <p class="text-gray-400 mt-2">Status: <span class="font-medium text-white">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></p>
        </div>
        <div class="text-right">
            <p class="text-gray-400">Priority: <span class="font-medium text-white">{{ ucfirst($task->priority) }}</span></p>
            <p class="text-gray-400">Due: <span class="font-medium text-white">{{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}</span></p>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-sm text-gray-300 mb-2">Description</h3>
        <div class="p-4 bg-white/5 rounded-lg">
            <p class="text-gray-200">{{ $task->description ?? 'No description provided.' }}</p>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-sm text-gray-300 mb-2">History</h3>
        <div class="space-y-3">
            @forelse($task->history as $h)
            <div class="p-3 bg-white/3 rounded-lg">
                <div class="text-sm text-gray-300">{{ $h->action }} — <span class="text-xs text-gray-400">{{ $h->created_at->diffForHumans() }}</span></div>
                @if($h->changes)
                <pre class="text-xs text-gray-400 mt-1">{{ $h->changes }}</pre>
                @endif
            </div>
            @empty
            <div class="p-4 bg-white/5 rounded-lg text-gray-400">No history yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
