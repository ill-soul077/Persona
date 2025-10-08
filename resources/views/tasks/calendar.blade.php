<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📅 Task Calendar
            </h2>
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
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                        <a href="{{ route('tasks.index', ['filter' => 'all', 'view' => 'calendar']) }}" 
                           class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All
                        </a>
                        <a href="{{ route('tasks.index', ['filter' => 'today', 'view' => 'calendar']) }}" 
                           class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'today' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Today
                        </a>
                        <a href="{{ route('tasks.index', ['filter' => 'week', 'view' => 'calendar']) }}" 
                           class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'week' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            This Week
                        </a>
                        <a href="{{ route('tasks.index', ['filter' => 'overdue', 'view' => 'calendar']) }}" 
                           class="px-4 py-2 rounded-lg font-medium transition {{ $filter === 'overdue' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Overdue
                        </a>
                        <a href="{{ route('tasks.index', ['filter' => 'completed', 'view' => 'calendar']) }}" 
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
                           class="px-4 py-2 rounded-lg font-medium transition bg-purple-600 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div id="calendar"></div>
            </div>

            <!-- Upcoming Tasks List -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Upcoming Tasks</h3>
                </div>
                
                @forelse($tasks as $task)
                    <div class="p-6 border-b border-gray-200 hover:bg-gray-50 transition {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
                        <div class="flex items-start gap-4">
                            <!-- Priority Badge -->
                            <div class="flex-shrink-0">
                                @php
                                    $priorityColors = [
                                        'high' => 'bg-red-100 text-red-800 border-red-300',
                                        'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                        'low' => 'bg-green-100 text-green-800 border-green-300',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $priorityColors[$task->priority] }}">
                                    {{ strtoupper($task->priority) }}
                                </span>
                            </div>

                            <!-- Task Content -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 {{ $task->status === 'completed' ? 'line-through' : '' }}">
                                            {{ $task->title }}
                                        </h3>
                                        @if($task->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ Str::limit($task->description, 150) }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="flex gap-2 ml-4">
                                        <a href="{{ route('tasks.show', $task) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('tasks.edit', $task) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                <!-- Task Meta Information -->
                                <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                                    @if($task->due_date)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="{{ $task->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                                {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                                @if($task->isOverdue())
                                                    (Overdue)
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="capitalize">{{ $task->status }}</span>
                                    </div>

                                    @if($task->tags && count($task->tags) > 0)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            <div class="flex gap-1 flex-wrap">
                                                @foreach($task->tags as $tag)
                                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-xs">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No tasks found</h3>
                        <p class="text-gray-600 mb-4">Get started by creating your first task</p>
                        <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
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

    @push('scripts')
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
                    return ['cursor-pointer', 'hover:opacity-80'];
                },
                height: 'auto',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                }
            });
            calendar.render();
        });

        // Quick Add Modal functionality (same as list view)
        function showQuickAddModal() {
            // Implement quick add modal
            alert('Quick add functionality coming soon!');
        }
    </script>
    @endpush
</x-app-layout>
