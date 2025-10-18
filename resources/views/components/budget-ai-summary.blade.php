@php
    $summary = session('budget_ai_summary');
    $error = session('budget_ai_summary_error');
    $throttled = session('budget_ai_summary_throttled');
@endphp

@if($error)
<div class="glass-card rounded-xl p-4 border border-red-400/40 bg-red-500/10 text-red-200 mb-6">
    <div class="flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14a7 7 0 000-14z" />
        </svg>
        <span><strong>Budget AI Summary Error:</strong> {{ $error }}</span>
    </div>
</div>
@endif

@if($summary)
<div class="glass-card rounded-xl p-6 border border-white/10 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-white">AI Budget Summary</h3>
        <form method="POST" action="{{ route(request()->routeIs('finance.*') ? 'finance.dashboard.budget.refresh' : 'dashboard.budget.refresh') }}">
            @csrf
            <button type="submit" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2 hover:scale-105 active:scale-95 transition-transform duration-200">
                <svg class="w-5 h-5 hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Refresh AI</span>
            </button>
        </form>
    </div>

    @if($throttled)
    <div class="mb-4 p-3 rounded-lg border border-blue-400/40 bg-blue-500/10 text-blue-100 text-sm">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Showing cached summary (updated recently). Refresh limit: 1/hour.
    </div>
    @endif

    @if(!empty($summary['fallback']))
    <div class="mb-4 p-3 rounded-lg border border-yellow-400/40 bg-yellow-500/10 text-yellow-100 text-sm">
        Using heuristic summary due to temporary AI quota limits.
    </div>
    @endif

    <p class="text-gray-200 leading-relaxed mb-4">{{ $summary['summary'] ?? '' }}</p>

    @if(!empty($summary['recommendations']))
    <div class="mt-4">
        <h4 class="text-white font-semibold mb-2">Recommendations</h4>
        <ul class="list-disc list-inside space-y-1 text-gray-200">
            @foreach($summary['recommendations'] as $rec)
                <li>
                    <span class="font-medium">{{ $rec['title'] ?? '' }}:</span>
                    <span>{{ $rec['detail'] ?? '' }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($summary['suggestedAllocations']))
    <div class="mt-4">
        <h4 class="text-white font-semibold mb-2">Suggested Allocations</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($summary['suggestedAllocations'] as $item)
                <div class="bg-white/5 border border-white/10 rounded-xl p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300">{{ $item['category'] ?? 'Category' }}</span>
                        <span class="text-white font-semibold">{{ $item['amount'] ?? 0 }}</span>
                    </div>
                    @if(!empty($item['reason']))
                    <p class="text-gray-400 text-sm mt-1">{{ $item['reason'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($summary['risks']))
    <div class="mt-4">
        <h4 class="text-white font-semibold mb-2">Risks / Watch-outs</h4>
        <ul class="list-disc list-inside space-y-1 text-gray-300">
            @foreach($summary['risks'] as $risk)
                <li>{{ $risk }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@else
<!-- If no summary yet, show a call-to-action -->
<div class="glass-card rounded-xl p-4 border border-white/10 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
        <p class="text-white font-medium">Get an AI-powered summary and recommendations for this month's budget.</p>
        <p class="text-gray-400 text-sm mt-1">Click refresh and we'll analyze your spending and remaining budget.</p>
    </div>
    <form method="POST" action="{{ route(request()->routeIs('finance.*') ? 'finance.dashboard.budget.refresh' : 'dashboard.budget.refresh') }}">
        @csrf
        <button type="submit" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2 hover:scale-105 active:scale-95 transition-transform duration-200 whitespace-nowrap">
            <svg class="w-5 h-5 hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Refresh AI</span>
        </button>
    </form>
</div>
@endif
