@extends('layouts.app-master')

@section('title', 'Tasks Calendar')
@section('page-icon', '📅')
@section('page-title', 'Task Calendar')

@section('content')
<!-- Calendar Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Task Calendar</h1>
            <p class="text-gray-300 mt-2">See and manage your tasks on a calendar view</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button onclick="showQuickAddModal()" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Quick Add</span>
            </button>
            <a href="{{ route('tasks.create') }}" class="glass-button bg-purple-600/20 text-purple-400 border-purple-500/30 px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>New Task</span>
            </a>
        </div>
    </div>
</div>

<!-- Main Calendar Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 animate-slide-up">
    <!-- Calendar Container -->
    <div class="lg:col-span-2">
        <div class="glass-card rounded-xl p-6">
            <div id="calendar" class="rounded-lg overflow-hidden"></div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Stats Overview -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Overview</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white/5 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Total</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-400">{{ $stats['today'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Today</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-400">{{ $stats['week'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">This Week</div>
                </div>
                <div class="bg-white/5 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-400">{{ $stats['overdue'] }}</div>
                    <div class="text-gray-400 text-sm mt-1">Overdue</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Filters</h3>
            <div class="flex flex-col gap-2">
                <a href="{{ route('tasks.index', ['filter' => 'all', 'view' => 'calendar']) }}" 
                   class="px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $filter === 'all' ? 'bg-purple-600 text-white shadow-lg' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    All Tasks
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'today', 'view' => 'calendar']) }}" 
                   class="px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $filter === 'today' ? 'bg-yellow-600 text-white shadow-lg' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Today
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'week', 'view' => 'calendar']) }}" 
                   class="px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $filter === 'week' ? 'bg-blue-600 text-white shadow-lg' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    This Week
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'overdue', 'view' => 'calendar']) }}" 
                   class="px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $filter === 'overdue' ? 'bg-red-600 text-white shadow-lg' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Overdue
                </a>
                <a href="{{ route('tasks.index', ['filter' => 'completed', 'view' => 'calendar']) }}" 
                   class="px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $filter === 'completed' ? 'bg-green-600 text-white shadow-lg' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Completed
                </a>
            </div>
        </div>

        <!-- Legend -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Priority Legend</h3>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <span class="text-white text-sm">High Priority</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <span class="text-white text-sm">Medium Priority</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span class="text-white text-sm">Low Priority</span>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <span class="text-white text-sm">Completed</span>
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">Upcoming Tasks</h3>
                <a href="{{ route('tasks.index') }}" class="text-purple-400 hover:text-purple-300 text-sm font-medium">
                    View All →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($tasks->take(5) as $task)
                <a href="{{ route('tasks.show', $task) }}" 
                   class="block p-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-200 group">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-white font-medium group-hover:text-purple-400 transition-colors {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                            {{ $task->title }}
                        </h4>
                        <span class="text-xs text-gray-400 bg-white/5 px-2 py-1 rounded">
                            {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                        </span>
                    </div>
                    @if($task->description)
                    <p class="text-gray-400 text-sm line-clamp-2">{{ $task->description }}</p>
                    @endif
                    <div class="flex justify-between items-center mt-3">
                        @if($task->priority === 'high')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                            High
                        </span>
                        @elseif($task->priority === 'medium')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                            Medium
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">
                            Low
                        </span>
                        @endif
                        
                        @if($task->status === 'completed')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                            Completed
                        </span>
                        @endif
                    </div>
                </a>
                @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-3">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 text-sm">No upcoming tasks</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('additional-scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '{{ route('tasks.calendar.feed') }}',
            eventClick: function(info) {
                window.location.href = '/tasks/' + info.event.id;
            },
            eventClassNames: function(arg) {
                return ['cursor-pointer', 'hover:opacity-80', 'border-0', 'rounded-lg'];
            },
            height: 'auto',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            themeSystem: 'standard',
            dayMaxEvents: 3,
            eventDisplay: 'block',
            views: {
                dayGridMonth: {
                    dayMaxEventRows: 3
                }
            }
        });
        calendar.render();
    });

    function showQuickAddModal() {
        // Implement quick add modal functionality
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="glass-card rounded-xl p-6 max-w-md w-full mx-4 animate-bounce-in">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Quick Add Task</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Title</label>
                            <input type="text" name="title" required 
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Due Date</label>
                            <input type="date" name="due_date" 
                                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div class="flex space-x-4">
                            <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition-colors">
                                Add Task
                            </button>
                            <button type="button" onclick="this.closest('.fixed').remove()" class="flex-1 bg-white/5 hover:bg-white/10 text-white py-3 rounded-lg font-medium transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
</script>

<style>
/* Custom FullCalendar styles to match the dashboard theme */
.fc {
    --fc-border-color: rgba(255, 255, 255, 0.1);
    --fc-page-bg-color: transparent;
    --fc-neutral-bg-color: rgba(255, 255, 255, 0.05);
    --fc-list-event-hover-bg-color: rgba(255, 255, 255, 0.1);
}

.fc .fc-toolbar {
    color: white;
}

.fc .fc-toolbar-title {
    color: white;
    font-weight: 600;
}

.fc .fc-button {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    font-weight: 500;
}

.fc .fc-button:hover {
    background-color: rgba(255, 255, 255, 0.2) !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: rgb(147, 51, 234) !important;
    border-color: rgb(147, 51, 234) !important;
}

.fc .fc-daygrid-day-number,
.fc .fc-col-header-cell-cushion {
    color: white;
    text-decoration: none;
}

.fc .fc-day-other .fc-daygrid-day-top {
    opacity: 0.5;
}

.fc .fc-daygrid-day.fc-day-today {
    background-color: rgba(147, 51, 234, 0.2) !important;
}

.fc-event {
    border: none !important;
    padding: 4px 8px !important;
    margin: 2px 0 !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
}

.fc-event-title {
    font-weight: 500 !important;
}
</style>
@endsection