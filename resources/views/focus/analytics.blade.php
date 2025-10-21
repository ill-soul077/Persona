@extends('layouts.app-master')

@section('title', 'Focus Analytics')
@section('page-icon', '📊')
@section('page-title', 'Focus Analytics')

@section('content')
<div class="animate-fade-in">
    <!-- Period Selector -->
    <div class="glass-card rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">Focus Analytics</h2>
            <div class="flex space-x-2">
                <a href="{{ route('focus.analytics', ['period' => 'week']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition {{ $period === 'week' ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Week
                </a>
                <a href="{{ route('focus.analytics', ['period' => 'month']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition {{ $period === 'month' ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Month
                </a>
                <a href="{{ route('focus.analytics', ['period' => 'year']) }}" 
                   class="px-4 py-2 rounded-lg font-medium transition {{ $period === 'year' ? 'bg-purple-600 text-white' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Year
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 animate-slide-up">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-300 text-sm font-medium">Total Sessions</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_sessions'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-300 text-sm font-medium">Focus Time</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_focus_minutes'] / 60, 1) }}h</p>
                    <p class="text-xs text-gray-400">{{ $stats['total_focus_minutes'] }} minutes</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-300 text-sm font-medium">Completed Pomodoros</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['completed_pomodoros'] }}</p>
                    @if($stats['work_sessions'] > 0)
                        <p class="text-xs text-green-400">{{ number_format(($stats['completed_pomodoros'] / $stats['work_sessions']) * 100, 1) }}% completion</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-300 text-sm font-medium">Avg. Session</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['average_session_length'], 1) }}m</p>
                    @if($stats['interrupted_sessions'] > 0)
                        <p class="text-xs text-red-400">{{ $stats['interrupted_sessions'] }} interrupted</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Breakdown Chart -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6">Daily Focus Time</h3>
            <div class="space-y-4">
                @forelse($dailyStats as $date => $dayStat)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-300">{{ \Carbon\Carbon::parse($date)->format('M d, D') }}</span>
                            <span class="text-sm font-medium text-white">{{ $dayStat['focus_minutes'] }} min</span>
                        </div>
                        <div class="relative w-full h-2 bg-white/10 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full transition-all"
                                 style="width: {{ min(($dayStat['focus_minutes'] / 120) * 100, 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">No focus sessions yet in this period</p>
                @endforelse
            </div>
        </div>

        <!-- Task Breakdown -->
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-xl font-bold text-white mb-6">Focus Time by Task</h3>
            <div class="space-y-4">
                @forelse($taskStats as $taskStat)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex-1 mr-4">
                                <div class="text-sm font-medium text-white truncate">{{ $taskStat['task_title'] }}</div>
                                <div class="text-xs text-gray-400">{{ $taskStat['sessions'] }} sessions</div>
                            </div>
                            <span class="text-sm font-medium text-purple-400">{{ $taskStat['total_minutes'] }} min</span>
                        </div>
                        <div class="relative w-full h-2 bg-white/10 rounded-full overflow-hidden">
                            @php
                                $maxMinutes = $taskStats->max('total_minutes');
                                $percentage = $maxMinutes > 0 ? ($taskStat['total_minutes'] / $maxMinutes) * 100 : 0;
                            @endphp
                            <div class="absolute inset-y-0 left-0 bg-purple-500 rounded-full transition-all"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">No task-specific focus sessions yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Achievement Badges -->
    <div class="glass-card rounded-xl p-6 mt-6">
        <h3 class="text-xl font-bold text-white mb-6">Achievements</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $achievements = [
                    ['title' => 'First Session', 'icon' => '🎯', 'unlocked' => $stats['total_sessions'] >= 1],
                    ['title' => '10 Pomodoros', 'icon' => '🔥', 'unlocked' => $stats['completed_pomodoros'] >= 10],
                    ['title' => '5 Hours Focus', 'icon' => '⚡', 'unlocked' => $stats['total_focus_minutes'] >= 300],
                    ['title' => 'Week Warrior', 'icon' => '🏆', 'unlocked' => $stats['total_sessions'] >= 20],
                    ['title' => '50 Pomodoros', 'icon' => '💎', 'unlocked' => $stats['completed_pomodoros'] >= 50],
                    ['title' => '20 Hours Focus', 'icon' => '👑', 'unlocked' => $stats['total_focus_minutes'] >= 1200],
                    ['title' => 'Task Master', 'icon' => '🎓', 'unlocked' => $stats['tasks_focused'] >= 10],
                    ['title' => 'Consistency', 'icon' => '📅', 'unlocked' => count($dailyStats) >= 7],
                ];
            @endphp

            @foreach($achievements as $achievement)
                <div class="text-center p-4 rounded-xl {{ $achievement['unlocked'] ? 'bg-purple-500/20' : 'bg-white/5 opacity-50' }}">
                    <div class="text-4xl mb-2">{{ $achievement['icon'] }}</div>
                    <div class="text-sm font-medium text-white">{{ $achievement['title'] }}</div>
                    @if($achievement['unlocked'])
                        <div class="text-xs text-green-400 mt-1">Unlocked!</div>
                    @else
                        <div class="text-xs text-gray-500 mt-1">Locked</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 flex justify-center space-x-4">
        <a href="{{ route('focus.index') }}" class="glass-button text-white px-6 py-3 rounded-xl font-medium">
            Start Focus Session
        </a>
        <a href="{{ route('tasks.index') }}" class="px-6 py-3 rounded-xl font-medium bg-white/5 hover:bg-white/10 text-white transition">
            Back to Tasks
        </a>
    </div>
</div>

@endsection
