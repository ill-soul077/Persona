@extends('layouts.app-master')

@section('title', 'Focus Mode')
@section('page-icon', '🎯')
@section('page-title', 'Focus Mode')

@section('content')
<div x-data="focusMode()" x-init="init()" @keydown.escape="exitFocusMode" class="animate-fade-in">
    <!-- Task Selection (shown before starting) -->
    <div x-show="!sessionActive && !showAnalytics" class="max-w-4xl mx-auto">
        <div class="glass-card rounded-xl p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">Ready to Focus?</h2>
                <p class="text-gray-300">Select a task and start your Pomodoro session</p>
            </div>

            <!-- Task Selection -->
            <div class="mb-6">
                <label class="block text-left text-sm font-medium text-gray-300 mb-2">Select Task (Optional)</label>
                <select x-model="selectedTaskId" class="w-full px-4 py-3 bg-gray-900/80 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent backdrop-blur-sm">
                    <option value="" class="bg-gray-900 text-white">No specific task</option>
                    @foreach($tasks as $taskOption)
                        <option value="{{ $taskOption->id }}" class="bg-gray-900 text-white" {{ isset($task) && $task->id == $taskOption->id ? 'selected' : '' }}>
                            {{ $taskOption->title }} 
                            @if($taskOption->due_date)
                                (Due: {{ \Carbon\Carbon::parse($taskOption->due_date)->format('M d') }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Timer Settings -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="glass-card rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Work</label>
                    <input type="number" x-model.number="settings.workDuration" min="1" max="60" 
                           class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white text-center focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <span class="text-xs text-gray-400">minutes</span>
                </div>
                <div class="glass-card rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Short Break</label>
                    <input type="number" x-model.number="settings.shortBreakDuration" min="1" max="30" 
                           class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white text-center focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <span class="text-xs text-gray-400">minutes</span>
                </div>
                <div class="glass-card rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Long Break</label>
                    <input type="number" x-model.number="settings.longBreakDuration" min="1" max="60" 
                           class="w-full px-4 py-2 bg-white/5 border border-white/20 rounded-lg text-white text-center focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <span class="text-xs text-gray-400">minutes</span>
                </div>
            </div>

            <button @click="startSession()" class="glass-button bg-purple-600/30 hover:bg-purple-600/40 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all duration-300 transform hover:scale-105">
                Start Focus Session
            </button>

            <!-- Analytics Link -->
            <div class="mt-4">
                <a href="{{ route('focus.analytics') }}" class="text-purple-400 hover:text-purple-300 font-medium flex items-center justify-center space-x-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>View Analytics</span>
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="glass-card rounded-xl p-6 text-center hover:bg-white/5 transition-all duration-300">
                <div class="text-3xl font-bold text-purple-400 mb-2" x-text="stats.todaySessions"></div>
                <div class="text-sm text-gray-400">Sessions Today</div>
            </div>
            <div class="glass-card rounded-xl p-6 text-center hover:bg-white/5 transition-all duration-300">
                <div class="text-3xl font-bold text-blue-400 mb-2" x-text="stats.todayMinutes"></div>
                <div class="text-sm text-gray-400">Minutes Focused</div>
            </div>
            <div class="glass-card rounded-xl p-6 text-center hover:bg-white/5 transition-all duration-300">
                <div class="text-3xl font-bold text-green-400 mb-2" x-text="stats.weekStreak"></div>
                <div class="text-sm text-gray-400">Day Streak</div>
            </div>
        </div>
    </div>

    <!-- Focus Mode Active (Full screen) -->
    <div x-show="sessionActive" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 bg-gradient-to-br from-gray-900 via-purple-900 to-violet-900">
        
        <!-- Centered Content Container -->
        <div class="flex flex-col items-center justify-center min-h-screen w-full py-8">
            <!-- Exit Button -->
            <button @click="confirmExit()" 
                    class="absolute top-6 right-6 text-gray-400 hover:text-white transition-all duration-300 p-3 rounded-lg hover:bg-white/10 z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Main Content - Vertically Centered -->
            <div class="flex-1 flex flex-col items-center justify-center w-full max-w-2xl px-6">
                <!-- Session Type Indicator -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center space-x-3 px-6 py-3 rounded-full glass-card border border-white/10" 
                         :class="{
                            'border-purple-500/50': currentSessionType === 'work',
                            'border-green-500/50': currentSessionType === 'short_break',
                            'border-blue-500/50': currentSessionType === 'long_break'
                         }">
                        <span class="w-3 h-3 rounded-full animate-pulse" 
                              :class="{
                                'bg-purple-400': currentSessionType === 'work',
                                'bg-green-400': currentSessionType === 'short_break',
                                'bg-blue-400': currentSessionType === 'long_break'
                              }"></span>
                        <span class="font-semibold text-lg text-white" x-text="sessionTypeLabel"></span>
                        <span class="text-gray-400 text-sm" x-text="`${pomodoroCount}/4`"></span>
                    </div>
                </div>

                <!-- Timer Display - Centered -->
                <div class="text-center mb-8 w-full">
                    <div class="text-8xl md:text-9xl font-bold text-white mb-6 font-mono tracking-tighter leading-none" 
                         x-text="displayTime"></div>
                    
                    <!-- Progress Bar -->
                    <div class="max-w-md mx-auto mb-4">
                        <div class="relative w-full h-3 bg-white/10 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-1000 ease-out"
                                 :style="`width: ${progress}%`"
                                 :class="{
                                    'bg-gradient-to-r from-purple-500 to-purple-600': currentSessionType === 'work',
                                    'bg-gradient-to-r from-green-500 to-green-600': currentSessionType === 'short_break',
                                    'bg-gradient-to-r from-blue-500 to-blue-600': currentSessionType === 'long_break'
                                 }"></div>
                        </div>
                    </div>
                    
                    <!-- Time Labels -->
                    <div class="flex justify-between text-sm text-gray-400 max-w-md mx-auto px-2">
                        <span>0:00</span>
                        <span x-text="Math.floor(totalSeconds / 60) + ':00'"></span>
                    </div>
                </div>

                <!-- Current Task -->
                <div x-show="currentTask" class="text-center mb-8 w-full max-w-2xl">
                    <div class="text-sm text-gray-400 mb-3 uppercase tracking-wider">Working on:</div>
                    <div class="text-xl md:text-2xl font-semibold text-white bg-white/5 rounded-xl p-4 md:p-6 border border-white/10" 
                         x-text="currentTask"></div>
                </div>

                <!-- Pomodoro Counter -->
                <div class="flex justify-center items-center space-x-3 mb-8">
                    <template x-for="i in 4" :key="i">
                        <div class="w-4 h-4 rounded-full transition-all duration-300" 
                             :class="i <= pomodoroCount ? 
                                    (currentSessionType === 'work' ? 'bg-purple-500' : 
                                     currentSessionType === 'short_break' ? 'bg-green-500' : 'bg-blue-500') : 
                                    'bg-white/20'"></div>
                    </template>
                </div>
            </div>

            <!-- Controls - Fixed at bottom -->
            <div class="w-full max-w-2xl px-6 pb-8">
                <div class="flex flex-col sm:flex-row justify-center gap-4 w-full">
                    <button @click="togglePause()" 
                            class="glass-button text-white px-6 py-4 rounded-xl font-semibold flex items-center justify-center space-x-3 transition-all duration-300 hover:scale-105 flex-1">
                        <svg x-show="!isPaused" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <svg x-show="isPaused" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="isPaused ? 'Resume' : 'Pause'"></span>
                    </button>

                    <button @click="skipSession()" 
                            class="px-6 py-4 rounded-xl font-semibold bg-white/10 hover:bg-white/20 text-white transition-all duration-300 border border-white/20 flex-1">
                        Skip
                    </button>

                    <button @click="completeSession(true)" 
                            class="px-6 py-4 rounded-xl font-semibold bg-red-500/20 hover:bg-red-500/30 text-red-400 transition-all duration-300 border border-red-500/30 flex-1">
                        End Session
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Complete Modal -->
    <div x-show="showCompleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm" @click.self="showCompleteModal = false">
        <div class="glass-card rounded-2xl p-8 max-w-md w-full mx-4 animate-bounce-in border border-white/10">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2" x-text="sessionTypeLabel + ' Complete!'"></h3>
                <p class="text-gray-300 mb-6 text-lg" x-text="completeMessage"></p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="continueToNextSession()" 
                            class="flex-1 glass-button bg-purple-600/30 hover:bg-purple-600/40 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-300">
                        Continue
                    </button>
                    <button @click="exitFocusMode()" 
                            class="flex-1 px-6 py-4 rounded-xl font-semibold bg-white/10 hover:bg-white/20 text-white transition-all duration-300 border border-white/20">
                        Exit
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function focusMode() {
    return {
        sessionActive: false,
        isPaused: false,
        showCompleteModal: false,
        showAnalytics: false,
        
        selectedTaskId: @json($task?->id ?? ''),
        currentTask: @json($task?->title ?? ''),
        currentTaskId: null,
        
        settings: {
            workDuration: 25,
            shortBreakDuration: 5,
            longBreakDuration: 15,
        },
        
        currentSessionType: 'work',
        pomodoroCount: 0,
        totalSeconds: 0,
        remainingSeconds: 0,
        startTime: null,
        currentSessionId: null,
        timerInterval: null,
        
        stats: {
            todaySessions: 0,
            todayMinutes: 0,
            weekStreak: 0,
        },
        
        init() {
            // Pre-select task if provided via query parameter
            @if(isset($task))
                this.selectedTaskId = {{ $task->id }};
            @endif
            
            this.loadStats();
            this.requestNotificationPermission();
            
            // Load saved settings from localStorage
            const savedSettings = localStorage.getItem('focusModeSettings');
            if (savedSettings) {
                this.settings = { ...this.settings, ...JSON.parse(savedSettings) };
            }
        },
        
        async requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                await Notification.requestPermission();
            }
        },
        
        async loadStats() {
            // Load today's stats from localStorage or API
            const stored = localStorage.getItem('focus_stats');
            if (stored) {
                this.stats = JSON.parse(stored);
            }
        },
        
        async startSession() {
            if (this.selectedTaskId) {
                this.currentTaskId = this.selectedTaskId;
                const taskElement = document.querySelector(`select option[value="${this.selectedTaskId}"]`);
                this.currentTask = taskElement ? taskElement.text.split('(')[0].trim() : '';
            } else {
                this.currentTaskId = null;
                this.currentTask = '';
            }
            
            // Save settings to localStorage
            localStorage.setItem('focusModeSettings', JSON.stringify(this.settings));
            
            this.currentSessionType = 'work';
            this.totalSeconds = this.settings.workDuration * 60;
            this.remainingSeconds = this.totalSeconds;
            this.startTime = Date.now();
            this.sessionActive = true;
            this.isPaused = false;
            
            // Start server session
            try {
                const response = await fetch('{{ route("focus.sessions.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        task_id: this.currentTaskId,
                        session_type: this.currentSessionType,
                        duration_minutes: this.settings.workDuration,
                        pomodoro_count: this.pomodoroCount
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.currentSessionId = data.session.id;
                }
            } catch (error) {
                console.error('Failed to start session:', error);
            }
            
            this.startTimer();
        },
        
        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            
            this.timerInterval = setInterval(() => {
                if (!this.isPaused) {
                    this.remainingSeconds--;
                    
                    if (this.remainingSeconds <= 0) {
                        this.handleSessionEnd();
                    }
                }
            }, 1000);
        },
        
        togglePause() {
            this.isPaused = !this.isPaused;
        },
        
        async handleSessionEnd() {
            clearInterval(this.timerInterval);
            
            const actualMinutes = Math.floor((Date.now() - this.startTime) / 60000);
            
            // Complete session on server
            if (this.currentSessionId) {
                try {
                    await fetch(`/focus/sessions/${this.currentSessionId}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            actual_minutes: actualMinutes,
                            interrupted: false
                        })
                    });
                } catch (error) {
                    console.error('Failed to complete session:', error);
                }
            }
            
            // Show notification
            this.showNotification();
            
            // Update stats
            if (this.currentSessionType === 'work') {
                this.pomodoroCount++;
                this.stats.todaySessions++;
                this.stats.todayMinutes += actualMinutes;
                localStorage.setItem('focus_stats', JSON.stringify(this.stats));
            }
            
            this.showCompleteModal = true;
        },
        
        showNotification() {
            if ('Notification' in window && Notification.permission === 'granted') {
                const messages = {
                    work: '🎉 Work session complete! Time for a break.',
                    short_break: '✨ Break over! Ready to focus again?',
                    long_break: '🌟 Long break complete! Feeling refreshed?'
                };
                
                new Notification('Focus Mode', {
                    body: messages[this.currentSessionType],
                    icon: '/favicon.ico',
                    badge: '/favicon.ico'
                });
            }
        },
        
        continueToNextSession() {
            this.showCompleteModal = false;
            
            // Determine next session type
            if (this.currentSessionType === 'work') {
                if (this.pomodoroCount % 4 === 0) {
                    this.currentSessionType = 'long_break';
                    this.totalSeconds = this.settings.longBreakDuration * 60;
                } else {
                    this.currentSessionType = 'short_break';
                    this.totalSeconds = this.settings.shortBreakDuration * 60;
                }
            } else {
                this.currentSessionType = 'work';
                this.totalSeconds = this.settings.workDuration * 60;
            }
            
            this.remainingSeconds = this.totalSeconds;
            this.startTime = Date.now();
            this.currentSessionId = null;
            
            // Start new server session
            this.startServerSession();
            this.startTimer();
        },
        
        async startServerSession() {
            try {
                const response = await fetch('{{ route("focus.sessions.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        task_id: this.currentTaskId,
                        session_type: this.currentSessionType,
                        duration_minutes: Math.floor(this.totalSeconds / 60),
                        pomodoro_count: this.pomodoroCount
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.currentSessionId = data.session.id;
                }
            } catch (error) {
                console.error('Failed to start session:', error);
            }
        },
        
        async skipSession() {
            if (confirm('Skip this session?')) {
                await this.completeSession(true);
                this.continueToNextSession();
            }
        },
        
        async completeSession(interrupted = false) {
            clearInterval(this.timerInterval);
            
            if (this.currentSessionId) {
                const actualMinutes = Math.floor((Date.now() - this.startTime) / 60000);
                
                try {
                    await fetch(`/focus/sessions/${this.currentSessionId}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            actual_minutes: actualMinutes,
                            interrupted: interrupted
                        })
                    });
                } catch (error) {
                    console.error('Failed to complete session:', error);
                }
            }
            
            if (interrupted) {
                this.exitFocusMode();
            }
        },
        
        confirmExit() {
            if (confirm('End the current session? Your progress will be saved.')) {
                this.completeSession(true);
            }
        },
        
        exitFocusMode() {
            clearInterval(this.timerInterval);
            this.sessionActive = false;
            this.showCompleteModal = false;
            this.pomodoroCount = 0;
            this.currentSessionId = null;
        },
        
        get displayTime() {
            const minutes = Math.floor(this.remainingSeconds / 60);
            const seconds = this.remainingSeconds % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },
        
        get progress() {
            return ((this.totalSeconds - this.remainingSeconds) / this.totalSeconds) * 100;
        },
        
        get sessionTypeLabel() {
            const labels = {
                work: 'Focus Time',
                short_break: 'Short Break',
                long_break: 'Long Break'
            };
            return labels[this.currentSessionType];
        },
        
        get completeMessage() {
            const messages = {
                work: 'Great work! Time for a well-deserved break.',
                short_break: 'Break time is over. Ready to dive back in?',
                long_break: 'You\'ve earned this break! Take your time.'
            };
            return messages[this.currentSessionType];
        }
    }
}
</script>

@endsection

@section('additional-styles')
<style>
    /* Improved dropdown styling */
    select option {
        background: #1f2937 !important;
        color: white !important;
        padding: 12px;
    }
    
    select:focus option {
        background: #374151 !important;
    }
    
    /* Enhanced timer animations */
    @keyframes pulse-glow {
        0%, 100% { 
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.4); 
        }
        50% { 
            box-shadow: 0 0 40px rgba(168, 85, 247, 0.8); 
        }
    }
    
    .font-mono {
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
    
    /* Smooth transitions for all interactive elements */
    button, select, input {
        transition: all 0.3s ease;
    }
    
    /* Improved glass card effect */
    .glass-card {
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
    }
    
    /* Ensure proper vertical centering */
    .min-h-screen {
        min-height: 100vh;
    }
</style>
@endsection