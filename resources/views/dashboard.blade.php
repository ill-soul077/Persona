@extends('layouts.app-master')

@section('title', 'Dashboard')
@section('page-icon', '🏠')
@section('page-title', 'Dashboard')

@section('content')
<!-- Dashboard Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Dashboard</h1>
            <p class="text-gray-300 mt-2">Your financial overview at a glance</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Add Transaction</span>
            </button>
        </div>
    </div>
</div>

<!-- Finance Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm font-medium">Total Income</p>
                <p class="text-3xl font-bold text-green-400">${{ number_format($totalIncome ?? 0, 2) }}</p>
                <p class="text-green-300 text-xs mt-1">+12% from last month</p>
            </div>
            <div class="text-green-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.1s;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm font-medium">Total Expenses</p>
                <p class="text-3xl font-bold text-red-400">${{ number_format($totalExpenses ?? 0, 2) }}</p>
                <p class="text-red-300 text-xs mt-1">+8% from last month</p>
            </div>
            <div class="text-red-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-xl p-6 animate-bounce-in" style="animation-delay: 0.2s;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm font-medium">Net Balance</p>
                <p class="text-3xl font-bold text-blue-400">${{ number_format(($totalIncome ?? 0) - ($totalExpenses ?? 0), 2) }}</p>
                <p class="text-blue-300 text-xs mt-1">Current month</p>
            </div>
            <div class="text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Budget Progress Section -->
<div class="animate-fade-in space-y-8">
    @include('components.budget-progress')
    @include('components.budget-ai-summary')
    
</div>

<!-- Today's & Tomorrow's Tasks -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Today's Tasks -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Today's Tasks</h3>
                    <p class="text-gray-400 text-sm">{{ now()->format('l, F j') }}</p>
                </div>
            </div>
            <a href="{{ route('tasks.index') }}" class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-medium">
                View All →
            </a>
        </div>
        
        @php
            $todaysTasks = \App\Models\Task::where('user_id', Auth::id())
                ->whereDate('due_date', now()->toDateString())
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->get();
        @endphp
        
        @if($todaysTasks->count() > 0)
        <div class="space-y-3">
            @foreach($todaysTasks->take(5) as $task)
            <div class="flex items-start space-x-3 p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors group" data-task-id="{{ $task->id }}">
                <div class="flex-shrink-0 mt-1">
                    <input type="checkbox" 
                           {{ $task->status === 'completed' ? 'checked' : '' }}
                           class="task-checkbox w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 focus:ring-offset-gray-900 cursor-pointer"
                           data-task-id="{{ $task->id }}">
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('tasks.show', $task->id) }}" class="block group-hover:text-blue-400 transition-colors">
                        <h4 class="task-title text-white font-medium {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                            {{ $task->title }}
                        </h4>
                        @if($task->description)
                        <p class="text-gray-400 text-sm mt-1 line-clamp-1">{{ $task->description }}</p>
                        @endif
                    </a>
                </div>
                <div class="flex-shrink-0">
                    @if($task->priority === 'high')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                        High
                    </span>
                    @elseif($task->priority === 'medium')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                        Medium
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">
                        Low
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No Tasks Today</h3>
            <p class="text-gray-400 mb-4">You're all caught up for today!</p>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Task
            </a>
        </div>
        @endif
    </div>

    <!-- Tomorrow's Tasks -->
    <div class="glass-card rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Tomorrow's Tasks</h3>
                    <p class="text-gray-400 text-sm">{{ now()->addDay()->format('l, F j') }}</p>
                </div>
            </div>
            <a href="{{ route('tasks.index') }}" class="text-purple-400 hover:text-purple-300 transition-colors text-sm font-medium">
                View All →
            </a>
        </div>
        
        @php
            $tomorrowsTasks = \App\Models\Task::where('user_id', Auth::id())
                ->whereDate('due_date', now()->addDay()->toDateString())
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->get();
        @endphp
        
        @if($tomorrowsTasks->count() > 0)
        <div class="space-y-3">
            @foreach($tomorrowsTasks->take(5) as $task)
            <div class="flex items-start space-x-3 p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors group" data-task-id="{{ $task->id }}">
                <div class="flex-shrink-0 mt-1">
                    <input type="checkbox" 
                           {{ $task->status === 'completed' ? 'checked' : '' }}
                           class="task-checkbox w-5 h-5 rounded border-gray-600 text-purple-500 focus:ring-purple-500 focus:ring-offset-gray-900 cursor-pointer"
                           data-task-id="{{ $task->id }}">
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('tasks.show', $task->id) }}" class="block group-hover:text-purple-400 transition-colors">
                        <h4 class="task-title text-white font-medium {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                            {{ $task->title }}
                        </h4>
                        @if($task->description)
                        <p class="text-gray-400 text-sm mt-1 line-clamp-1">{{ $task->description }}</p>
                        @endif
                    </a>
                </div>
                <div class="flex-shrink-0">
                    @if($task->priority === 'high')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                        High
                    </span>
                    @elseif($task->priority === 'medium')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                        Medium
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">
                        Low
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No Tasks Tomorrow</h3>
            <p class="text-gray-400 mb-4">Plan ahead for tomorrow</p>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Task
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Recent Transactions -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-white">Recent Transactions</h3>
        <a href="{{ route('finance.transactions.index') }}" class="text-blue-400 hover:text-blue-300 transition-colors text-sm font-medium">
            View All →
        </a>
    </div>
    
    @if(isset($recentTransactions) && $recentTransactions->count() > 0)
    <div class="space-y-4">
        @foreach($recentTransactions->take(5) as $transaction)
        <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-full {{ $transaction->type === 'income' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }} flex items-center justify-center">
                    @if($transaction->type === 'income')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <h4 class="text-white font-medium">{{ $transaction->description }}</h4>
                    <p class="text-gray-400 text-sm">{{ $transaction->date->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-lg font-bold {{ $transaction->type === 'income' ? 'text-green-400' : 'text-red-400' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                </div>
                <div class="text-gray-400 text-sm">{{ $transaction->category?->name ?? 'Uncategorized' }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8">
        <div class="text-gray-400 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-white mb-2">No Transactions</h3>
        <p class="text-gray-400">Your recent transactions will appear here</p>
    </div>
    @endif
</div>

<!-- Quick Actions -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <h3 class="text-xl font-bold text-white mb-6">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('finance.transactions.index') }}" class="flex items-center space-x-3 p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-white font-medium">View All Transactions</span>
        </a>
        
        <a href="{{ route('finance.reports') }}" class="flex items-center space-x-3 p-4 bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-white font-medium">Generate Reports</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle task checkbox toggle
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const taskId = this.getAttribute('data-task-id');
            const isChecked = this.checked;
            const taskContainer = this.closest('[data-task-id]');
            const taskTitle = taskContainer.querySelector('.task-title');
            
            // Toggle checkbox immediately for better UX
            this.checked = !isChecked;
            
            // Make AJAX request to toggle status
            fetch(`/tasks/${taskId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update task title appearance
                    if (data.status === 'completed') {
                        taskTitle.classList.add('line-through', 'text-gray-500');
                    } else {
                        taskTitle.classList.remove('line-through', 'text-gray-500');
                    }
                    
                    // Show success notification
                    showNotification(data.message, 'success');
                } else {
                    // Revert checkbox on error
                    this.checked = isChecked;
                    showNotification(data.message || 'Failed to update task', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert checkbox on error
                this.checked = isChecked;
                showNotification('An error occurred while updating the task', 'error');
            });
        });
    });
    
    // Simple notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
</script>

@endsection