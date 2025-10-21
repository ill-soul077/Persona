@extends('layouts.app-master')

@section('title', 'Finance Dashboard')
@section('page-icon', '💰')
@section('page-title', 'Finance')

@section('additional-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
@endsection



@section('content')
<!-- Finance Dashboard Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Finance Dashboard</h1>
            <p class="text-gray-300 mt-2">Track your income, expenses, and financial goals</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <form method="POST" action="{{ route('finance.dashboard.budget.refresh') }}">
                @csrf
                <button type="submit" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Refresh Data</span>
                </button>
            </form>
            <a href="{{ route('finance.transactions.create') }}" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Transaction</span>
            </a>
        </div>
    </div>
</div>

<!-- Monthly Budget Progress Widget -->
<div class="space-y-8">
    @include('components.budget-progress')
    @include('components.budget-ai-summary')
</div>

<!-- Finance Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
    <div class="glass-card rounded-xl p-6 animate-bounce-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm font-medium">Total Income</p>
                <p class="text-3xl font-bold text-green-400">${{ number_format($totalIncome ?? 0, 2) }}</p>
                <p class="text-green-300 text-xs mt-1">This month</p>
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
                <p class="text-3xl font-bold text-red-400">${{ number_format($totalExpense ?? 0, 2) }}</p>
                <p class="text-red-300 text-xs mt-1">This month</p>
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
                <p class="text-gray-300 text-sm font-medium">Balance</p>
                <p class="text-3xl font-bold text-blue-400">${{ number_format($balance ?? 0, 2) }}</p>
                <p class="text-blue-300 text-xs mt-1">Current balance</p>
            </div>
            <div class="text-blue-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in">
    <!-- Expense Breakdown Chart -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4">Expense Breakdown</h3>
        <div class="h-64 flex items-center justify-center">
            @if(isset($expenseBreakdown) && !$expenseBreakdown->isEmpty())
                <canvas id="expenseChart" width="400" height="200"></canvas>
            @else
                <div class="text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-gray-400">No expense data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-xl font-bold text-white mb-4">Monthly Trend</h3>
        <div class="h-64 flex items-center justify-center">
            <canvas id="trendChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Income vs Expense Comparison Chart -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <h3 class="text-xl font-bold text-white mb-4">Income vs Expense Comparison</h3>
    <div class="h-80 flex items-center justify-center">
        <canvas id="comparisonChart" width="400" height="300"></canvas>
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Chart.js to load
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    } else {
        console.log('Chart.js loaded successfully');
    }

    // Chart.js configuration for Expense Breakdown
@if(isset($expenseBreakdown) && !$expenseBreakdown->isEmpty())
const expenseBreakdownData = @json($expenseBreakdown);
const expenseBreakdownLabels = Object.keys(expenseBreakdownData);
const expenseBreakdownValues = Object.values(expenseBreakdownData);

console.log('Expense Breakdown Data:', { labels: expenseBreakdownLabels, values: expenseBreakdownValues });

if (expenseBreakdownLabels.length > 0) {
    const expenseChartElement = document.getElementById('expenseChart');
    if (expenseChartElement) {
        const ctx1 = expenseChartElement.getContext('2d');
        new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: expenseBreakdownLabels,
            datasets: [{
                data: expenseBreakdownValues,
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(147, 51, 234, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#f8fafc',
                        padding: 20
                    }
                }
            }
        }
    });
    } else {
        console.error('Expense chart canvas element not found');
    }
} else {
    console.warn('No expense breakdown data available');
}
@else
console.log('Expense breakdown data is empty or not set');
@endif

// Monthly Trend Chart with real data
@if(isset($monthlyTrend) && isset($monthLabels) && count($monthlyTrend) > 0)
const monthLabels = @json($monthLabels);
const monthlyTrendData = @json($monthlyTrend);

console.log('Monthly Trend Data:', monthlyTrendData);
console.log('Month Labels:', monthLabels);

const trendIncomeData = monthlyTrendData.map(month => parseFloat(month.income) || 0);
const trendExpenseData = monthlyTrendData.map(month => parseFloat(month.expense) || 0);
const trendBalanceData = monthlyTrendData.map(month => parseFloat(month.balance) || 0);

console.log('Income Data:', trendIncomeData);
console.log('Expense Data:', trendExpenseData);
console.log('Balance Data:', trendBalanceData);

const trendChartElement = document.getElementById('trendChart');
if (trendChartElement) {
    const ctx2 = trendChartElement.getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Income',
            data: trendIncomeData,
            borderColor: 'rgba(34, 197, 94, 1)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Expenses',
            data: trendExpenseData,
            borderColor: 'rgba(239, 68, 68, 1)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#f8fafc',
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '$' + context.parsed.y.toFixed(2);
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#f8fafc',
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            x: {
                ticks: {
                    color: '#f8fafc'
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            }
        }
    }
});
}

// Income vs Expense Comparison Bar Chart
const comparisonChartElement = document.getElementById('comparisonChart');
if (comparisonChartElement) {
    const ctx3 = comparisonChartElement.getContext('2d');
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Income',
            data: trendIncomeData,
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 2
        }, {
            label: 'Expenses',
            data: trendExpenseData,
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: 'rgba(239, 68, 68, 1)',
            borderWidth: 2
        }, {
            label: 'Balance',
            data: trendBalanceData,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            type: 'line',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#f8fafc',
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '$' + context.parsed.y.toFixed(2);
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#f8fafc',
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            },
            x: {
                ticks: {
                    color: '#f8fafc'
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                }
            }
        }
    }
});
}
@else
    console.error('Monthly trend data not available or empty');
@endif

}); // End DOMContentLoaded
</script>
@endsection