@extends('layouts.app-master')

@section('title', 'Reports')
@section('page-icon', '📊')
@section('page-title', 'Reports')

@section('content')
<!-- Reports Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Financial Reports</h1>
            <p class="text-gray-300 mt-2">Analyze your financial performance and trends</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button onclick="window.print()" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2 hover:bg-white/20 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Print Report</span>
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics Summary -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Income (This Month)</span>
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-green-400">৳{{ number_format($currentMonthIncome, 2) }}</h3>
        <p class="text-gray-400 text-xs mt-2">{{ date('F Y') }}</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Expenses (This Month)</span>
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-red-400">৳{{ number_format($currentMonthExpenses, 2) }}</h3>
        <p class="text-gray-400 text-xs mt-2">{{ date('F Y') }}</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Net Savings</span>
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold {{ $currentMonthNet >= 0 ? 'text-blue-400' : 'text-red-400' }}">৳{{ number_format($currentMonthNet, 2) }}</h3>
        <p class="text-gray-400 text-xs mt-2">{{ $currentMonthNet >= 0 ? 'Surplus' : 'Deficit' }}</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Savings Rate</span>
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-purple-400">{{ number_format($savingsRate, 1) }}%</h3>
        <p class="text-gray-400 text-xs mt-2">Of total income</p>
    </div>
</div>

<!-- Interactive Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Income vs Expenses Trend -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            6-Month Trend
        </h3>
        <div class="h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Expenses by Category -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
            </svg>
            Expenses by Category
        </h3>
        <div class="h-64">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</div>

<!-- Income Sources & Top Expenses -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Income Sources -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Income Sources
        </h3>
        @if($incomeBySource->count() > 0)
            <div class="space-y-3">
                @foreach($incomeBySource as $source)
                <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg hover:bg-white/10 transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="text-white font-medium">{{ $source->name }}</span>
                    </div>
                    <span class="text-green-400 font-semibold">৳{{ number_format($source->total, 2) }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-400">No income recorded this month</p>
            </div>
        @endif
    </div>

    <!-- Top 5 Expenses -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
            </svg>
            Top 5 Expenses
        </h3>
        @if($topExpenses->count() > 0)
            <div class="space-y-3">
                @foreach($topExpenses as $index => $expense)
                <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg hover:bg-white/10 transition-all">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-red-500/20 rounded-full flex items-center justify-center text-red-400 text-sm font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $expense->description ?? 'Expense' }}</p>
                            <p class="text-gray-400 text-xs">{{ $expense->date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <span class="text-red-400 font-semibold">৳{{ number_format($expense->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-400">No expenses recorded this month</p>
            </div>
        @endif
    </div>
</div>

<!-- Year Comparison -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <h3 class="text-xl font-bold text-white mb-4 flex items-center">
        <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Year-over-Year Comparison
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white/5 rounded-xl p-4 border border-white/10">
            <p class="text-gray-300 text-sm mb-2">This Year ({{ date('Y') }})</p>
            <p class="text-2xl font-bold text-white">৳{{ number_format($currentYearTotal, 2) }}</p>
        </div>
        <div class="bg-white/5 rounded-xl p-4 border border-white/10">
            <p class="text-gray-300 text-sm mb-2">Last Year ({{ date('Y') - 1 }})</p>
            <p class="text-2xl font-bold text-white">৳{{ number_format($lastYearTotal, 2) }}</p>
        </div>
        <div class="bg-white/5 rounded-xl p-4 border border-white/10">
            <p class="text-gray-300 text-sm mb-2">Change</p>
            <p class="text-2xl font-bold {{ $yearOverYearChange >= 0 ? 'text-red-400' : 'text-green-400' }}">
                {{ $yearOverYearChange >= 0 ? '+' : '' }}{{ number_format($yearOverYearChange, 1) }}%
            </p>
        </div>
    </div>
</div>

<!-- Productivity Analytics Section -->
<div class="glass-card rounded-xl p-6 mt-8 animate-fade-in">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center">
            <svg class="w-8 h-8 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Productivity Analytics
        </h2>
        <div class="text-gray-400 text-sm">
            Last {{ $taskPeriod }} days
        </div>
    </div>
</div>

<!-- Task Metrics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Tasks Completed Today</span>
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-green-400">{{ $taskMetrics['completed_today'] }}</h3>
        <p class="text-gray-400 text-xs mt-2">{{ now()->format('l') }}</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Weekly Completion Rate</span>
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-blue-400">{{ $taskMetrics['weekly_rate'] }}%</h3>
        <p class="text-gray-400 text-xs mt-2">This week's performance</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Current Streak</span>
            <span class="text-2xl">🔥</span>
        </div>
        <h3 class="text-3xl font-bold text-orange-400">{{ $taskMetrics['current_streak'] }}</h3>
        <p class="text-gray-400 text-xs mt-2">{{ $taskMetrics['current_streak'] === 1 ? 'day' : 'days' }} in a row</p>
    </div>

    <div class="glass-card rounded-xl p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Avg Tasks / Day</span>
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="text-3xl font-bold text-purple-400">{{ $taskMetrics['avg_per_day'] }}</h3>
        <p class="text-gray-400 text-xs mt-2">Last 30 days average</p>
    </div>
</div>

<!-- Task Analytics Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Completion Trend -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            Completion Trend
        </h3>
        <div class="h-64">
            <canvas id="completionTrendChart"></canvas>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Priority Distribution
        </h3>
        <div class="h-64">
            <canvas id="priorityChart"></canvas>
        </div>
    </div>

    <!-- Productivity Heatmap -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Productivity by Hour
        </h3>
        <div class="h-64">
            <canvas id="heatmapChart"></canvas>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Task Categories
        </h3>
        <div class="h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Smart Insights -->
@if(count($smartInsights) > 0)
<div class="grid grid-cols-1 md:grid-cols-{{ min(count($smartInsights), 3) }} gap-6 animate-fade-in">
    @foreach($smartInsights as $insight)
    <div class="glass-card rounded-xl p-6 border-l-4 border-purple-500">
        <div class="flex items-start space-x-3">
            <span class="text-3xl">{{ $insight['icon'] }}</span>
            <div>
                <h4 class="text-lg font-bold text-white mb-2">{{ $insight['title'] }}</h4>
                <p class="text-gray-300 text-sm">{{ $insight['message'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.color = '#9CA3AF';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    
    // 6-Month Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyTrend, 'month')) !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! json_encode(array_column($monthlyTrend, 'income')) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Expenses',
                    data: {!! json_encode(array_column($monthlyTrend, 'expense')) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#fff',
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF'
                    }
                }
            }
        }
    });
    
    // Expenses by Category Chart
    @if($expensesByCategory->count() > 0)
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($expensesByCategory->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($expensesByCategory->pluck('total')) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(148, 163, 184, 0.8)'
                ],
                borderColor: 'rgba(0, 0, 0, 0.8)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        color: '#fff',
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ৳' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    @else
    // Show message when no expense data
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    expenseCtx.font = '16px sans-serif';
    expenseCtx.fillStyle = '#9CA3AF';
    expenseCtx.textAlign = 'center';
    expenseCtx.fillText('No expense data for this month', expenseCtx.canvas.width / 2, expenseCtx.canvas.height / 2);
    @endif

    // ============================================
    // PRODUCTIVITY ANALYTICS CHARTS
    // ============================================

    // Completion Trend Chart
    const completionTrendCtx = document.getElementById('completionTrendChart').getContext('2d');
    new Chart(completionTrendCtx, {
        type: 'line',
        data: {
            labels: @json($completionTrend['labels']),
            datasets: [{
                label: 'Tasks Completed',
                data: @json($completionTrend['data']),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(34, 197, 94)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    // Priority Distribution Chart
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');
    new Chart(priorityCtx, {
        type: 'pie',
        data: {
            labels: @json($priorityDistribution['labels']),
            datasets: [{
                data: @json($priorityDistribution['data']),
                backgroundColor: @json($priorityDistribution['colors']),
                borderColor: 'rgba(0, 0, 0, 0.8)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        color: '#fff',
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' tasks (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Productivity Heatmap Chart
    const heatmapCtx = document.getElementById('heatmapChart').getContext('2d');
    new Chart(heatmapCtx, {
        type: 'bar',
        data: {
            labels: @json($productivityHeatmap['labels']),
            datasets: [{
                label: 'Tasks Completed',
                data: @json($productivityHeatmap['data']),
                backgroundColor: function(context) {
                    const value = context.parsed.y;
                    if (value === 0) return 'rgba(107, 114, 128, 0.3)';
                    if (value <= 2) return 'rgba(59, 130, 246, 0.6)';
                    if (value <= 5) return 'rgba(139, 92, 246, 0.7)';
                    return 'rgba(168, 85, 247, 0.9)';
                },
                borderColor: 'rgba(139, 92, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            return 'Hour: ' + context[0].label;
                        },
                        label: function(context) {
                            return 'Tasks: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9CA3AF',
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxRotation: 90,
                        minRotation: 45,
                        font: {
                            size: 9
                        }
                    }
                }
            }
        }
    });

    // Task Category Breakdown Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($taskCategoryBreakdown['labels']),
            datasets: [{
                data: @json($taskCategoryBreakdown['data']),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(148, 163, 184, 0.8)'
                ],
                borderColor: 'rgba(0, 0, 0, 0.8)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        color: '#fff',
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' tasks (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
