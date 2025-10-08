<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Finance Dashboard
            </h2>
            
            <!-- Date Range Selector -->
            <form method="GET" class="flex items-center space-x-2" x-data="{ updating: false }">
                <input type="date" 
                       name="start_date" 
                       value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                       class="rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                       @change="$el.form.submit(); updating = true">
                <span class="text-gray-500 dark:text-gray-400">to</span>
                <input type="date" 
                       name="end_date" 
                       value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                       class="rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                       @change="$el.form.submit(); updating = true">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg x-show="updating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Update
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
<!-- Summary Cards -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <!-- Total Income Card -->
    <div class="overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-green-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Total Income</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">৳{{ number_format($totalIncome, 2) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Expense Card -->
    <div class="overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-red-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Total Expense</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">৳{{ number_format($totalExpense, 2) }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md {{ $balance >= 0 ? 'bg-blue-500' : 'bg-orange-500' }} p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Balance</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold {{ $balance >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                                ৳{{ number_format($balance, 2) }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings Rate Card -->
    <div class="overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="rounded-md bg-purple-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500">Savings Rate</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ $totalIncome > 0 ? number_format(($balance / $totalIncome) * 100, 1) : 0 }}%
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Lists Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Expense Breakdown Chart -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Expense Breakdown</h2>
                <button onclick="refreshChart()" class="text-sm text-indigo-600 hover:text-indigo-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
            
            @if($expenseBreakdown->isNotEmpty())
            <div class="relative h-80">
                <canvas id="expenseChart"></canvas>
            </div>
            
            <!-- Legend -->
            <div id="chartLegend" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2"></div>
            @else
            <div class="flex flex-col items-center justify-center h-80 text-gray-400">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <p class="text-sm">No expenses recorded for this period</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
                <a href="{{ route('finance.transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all</a>
            </div>
            
            @if($recentTransactions->isNotEmpty())
            <ul role="list" class="divide-y divide-gray-200">
                @foreach($recentTransactions as $transaction)
                <li class="py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $transaction->category?->name ?? 'Uncategorized' }}
                            </p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ $transaction->description ?? $transaction->date->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                            </span>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-sm">No transactions yet</p>
            </div>
            @endif
        </div>
    </div>
</div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
@if($expenseBreakdown->isNotEmpty())
// Chart.js configuration
const chartData = {!! json_encode($expenseBreakdown) !!};
const labels = Object.keys(chartData);
const data = Object.values(chartData);

// Color palette
const colors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1',
    '#8B5CF6', '#EC4899', '#F97316', '#14B8A6', '#F43F5E'
];

const ctx = document.getElementById('expenseChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors.slice(0, labels.length),
            borderWidth: 2,
            borderColor: '#ffffff'
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
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ৳${value.toFixed(2)} (${percentage}%)`;
                    }
                }
            }
        },
        onClick: (event, elements) => {
            if (elements.length > 0) {
                const index = elements[0].index;
                const category = labels[index];
                showCategoryDrilldown(category);
            }
        }
    }
});

// Generate legend
const legendContainer = document.getElementById('chartLegend');
labels.forEach((label, index) => {
    const value = data[index];
    const total = data.reduce((a, b) => a + b, 0);
    const percentage = ((value / total) * 100).toFixed(1);
    
    const legendItem = document.createElement('div');
    legendItem.className = 'flex items-center text-sm cursor-pointer hover:bg-gray-50 p-2 rounded';
    legendItem.onclick = () => showCategoryDrilldown(label);
    legendItem.innerHTML = `
        <span class="w-3 h-3 rounded-full mr-2" style="background-color: ${colors[index]}"></span>
        <span class="flex-1 truncate">${label}</span>
        <span class="text-gray-600 font-medium ml-2">${percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
});

function showCategoryDrilldown(category) {
    // Implement drill-down modal (will add in next iteration)
    alert(`Showing transactions for: ${category}`);
}

function refreshChart() {
    window.location.reload();
}
@endif
</script>
</x-app-layout>
