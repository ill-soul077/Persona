@extends('layouts.main')
@section('content_only', true)

@section('content')
<div style="max-width:1200px; margin: 0 auto; padding: 0 20px 40px;">
    <div class="card pad" style="margin-bottom:18px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <div>
                <h2 class="title" style="margin:0">Finance Dashboard</h2>
                <p class="subtitle" style="margin:2px 0 0">Analyze income and expenses at a glance</p>
            </div>
            <form method="GET" style="display:flex; align-items:center; gap:8px;">
                <input class="input" type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                <span style="color:var(--muted)">to</span>
                <input class="input" type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                <button class="btn" type="submit"><i class="fa-solid fa-rotate"></i> Update</button>
            </form>
        </div>
    </div>

    <div class="stats stats-4">
        <div class="stat income">
            <div class="label">Total Income</div>
            <div class="value" data-counter data-target="{{ (int) round($totalIncome) }}">{{ number_format($totalIncome, 0) }}</div>
            <div style="color:var(--muted); font-size:12px">BDT</div>
        </div>
        <div class="stat expense">
            <div class="label">Total Expense</div>
            <div class="value" data-counter data-target="{{ (int) round($totalExpense) }}">{{ number_format($totalExpense, 0) }}</div>
            <div style="color:var(--muted); font-size:12px">BDT</div>
        </div>
        <div class="stat balance">
            <div class="label">Balance</div>
            <div class="value" data-counter data-target="{{ (int) round($balance) }}">{{ number_format($balance, 0) }}</div>
            <div style="color:var(--muted); font-size:12px">BDT</div>
        </div>
        <div class="stat" style="border-left: 3px solid rgba(245, 158, 11, .5)">
            <div class="label">Savings Rate</div>
            <div class="value">{{ $totalIncome > 0 ? number_format(($balance / $totalIncome) * 100, 1) : 0 }}%</div>
            <div style="color:var(--muted); font-size:12px">This period</div>
        </div>
    </div>

    <div class="page-grid">
        <div>
            <div class="card pad">
                <div class="flex items-center justify-between mb-4" style="display:flex; align-items:center; justify-content:space-between;">
                    <h2 class="text-lg font-semibold">Expense Breakdown</h2>
                    <button onclick="refreshChart()" class="btn secondary"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>

                @if($expenseBreakdown->isNotEmpty())
                    <div class="relative" style="height:320px;">
                        <canvas id="expenseChart" aria-label="Expense chart"></canvas>
                    </div>
                    <div id="chartLegend" class="mt-4 chart-legend"></div>
                @else
                    <div class="flex flex-col items-center justify-center" style="height:320px; color:var(--muted); display:flex; align-items:center; justify-content:center;">
                        <svg class="w-16 h-16 mb-4" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2z"/>
                        </svg>
                        <p class="text-sm">No expenses recorded for this period</p>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="card pad">
                <div class="flex items-center justify-between mb-4" style="display:flex; align-items:center; justify-content:space-between;">
                    <h2 class="text-lg font-semibold">Recent Transactions</h2>
                    <a href="{{ route('finance.transactions.index') }}" class="btn secondary" style="padding:8px 10px">View all</a>
                </div>

                @if($recentTransactions->isNotEmpty())
                    <ul role="list" style="list-style:none; margin:0; padding:0;">
                        @foreach($recentTransactions as $transaction)
                        <li class="py-3" style="border-top:1px solid rgba(255,255,255,.06)">
                            <div class="flex items-center justify-between" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">{{ $transaction->category?->name ?? 'Uncategorized' }}</p>
                                    <p class="text-xs" style="color:var(--muted)">{{ $transaction->description ?? $transaction->date->format('M d, Y') }}</p>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="text-sm font-semibold" style="color: {{ $transaction->type === 'income' ? '#86efac' : '#fca5a5' }};">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12" style="color:var(--muted);">
                        <svg class="w-12 h-12 mx-auto mb-4" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-sm">No recent transactions</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
@if($expenseBreakdown->isNotEmpty())
<script>
// Chart.js configuration
const chartData = @json($expenseBreakdown);
const labels = Object.keys(chartData);
const data = Object.values(chartData);

// Color palette
const colors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#22D3EE'
];

const ctx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: labels.map((_, i) => colors[i % colors.length]),
            borderWidth: 2,
            borderColor: 'rgba(255, 255, 255, .06)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ৳${Number(value).toFixed(2)} (${percentage}%)`;
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
    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
    const legendItem = document.createElement('div');
    legendItem.style.display = 'flex';
    legendItem.style.alignItems = 'center';
    legendItem.style.gap = '10px';
    legendItem.style.padding = '8px';
    legendItem.style.borderRadius = '10px';
    legendItem.style.cursor = 'pointer';
    legendItem.onclick = () => showCategoryDrilldown(label);
    legendItem.innerHTML = `
        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${colors[index % colors.length]}"></span>
        <span style="flex:1">${label}</span>
        <span style="color:var(--muted)">${percentage}%</span>
    `;
    legendContainer.appendChild(legendItem);
});

function showCategoryDrilldown(category) {
    // Placeholder for future modal/detail
    alert(`Showing transactions for: ${category}`);
}

function refreshChart() {
    window.location.reload();
}
</script>
@else
<script>
function refreshChart() { window.location.reload(); }
</script>
@endif
        

