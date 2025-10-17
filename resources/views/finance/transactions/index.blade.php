@extends('layouts.app-master')

@section('title', 'Transactions')
@section('page-icon', '💳')
@section('page-title', 'Transactions')

@section('additional-scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

@section('action-buttons')
<a href="{{ route('finance.transactions.create') }}" class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Add Transaction</span>
</a>
@endsection

@section('content')
<!-- Page Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">Transaction History</h1>
            <p class="text-gray-300 mt-2">Manage and track all your financial transactions</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                <span>Export</span>
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <h3 class="text-lg font-semibold text-white mb-4 flex items-center justify-between">
        <span>Filter Transactions</span>
        @if(request()->hasAny(['search', 'type', 'category_id', 'start_date', 'end_date']))
        <a href="{{ route('finance.transactions.index') }}" class="text-sm text-blue-300 hover:text-blue-200 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>Clear Filters</span>
        </a>
        @endif
    </h3>
    <form method="GET" action="{{ route('finance.transactions.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Search</label>
                <input 
                    type="text" 
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search transactions..." 
                    class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="" class="bg-gray-800">All Types</option>
                    <option value="income" class="bg-gray-800" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" class="bg-gray-800" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Category</label>
                <select name="category_id" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="" class="bg-gray-800">All Categories</option>
                    @if(isset($expenseCategories))
                        <optgroup label="Expense Categories" class="bg-gray-800">
                            @foreach($expenseCategories as $category)
                                <option value="{{ $category->id }}" class="bg-gray-800" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @if($category->children->isNotEmpty())
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" class="bg-gray-800" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;&nbsp;↳ {{ $child->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </optgroup>
                    @endif
                    @if(isset($incomeSources))
                        <optgroup label="Income Sources" class="bg-gray-800">
                            @foreach($incomeSources as $source)
                                <option value="{{ $source->id }}" class="bg-gray-800" {{ request('category_id') == $source->id ? 'selected' : '' }}>
                                    {{ $source->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">From Date</label>
                <input 
                    type="date" 
                    name="start_date"
                    value="{{ request('start_date') }}"
                    class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">To Date</label>
                <input 
                    type="date" 
                    name="end_date"
                    value="{{ request('end_date') }}"
                    class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="flex items-center justify-between pt-4 border-t border-white/10">
            <div class="text-sm text-gray-300">
                @if($transactions->total() > 0)
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} transactions
                @else
                    No transactions found
                @endif
            </div>
            <div class="flex items-center space-x-3">
                @if(request()->hasAny(['search', 'type', 'category_id', 'start_date', 'end_date']))
                <a href="{{ route('finance.transactions.index') }}" 
                   class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-all">
                    Reset
                </a>
                @endif
                <button type="submit" 
                        class="glass-button text-white px-6 py-2 rounded-xl font-medium flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Apply Filters</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="glass-card rounded-xl overflow-hidden animate-fade-in">
    @if($transactions->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-white/10 backdrop-blur-sm">
                <tr class="border-b border-white/20">
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                        Date
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                        Type
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                        Category
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-200 uppercase tracking-wider">
                        Amount
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach($transactions as $transaction)
                <tr class="hover:bg-white/5 transition-all duration-200 backdrop-blur-sm">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">
                            {{ $transaction->date->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-gray-300">
                            {{ $transaction->date->format('l') }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($transaction->type === 'income')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                            </svg>
                            Income
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-300 border border-red-500/30">
                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"></path>
                            </svg>
                            Expense
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-white font-medium">
                            {{ $transaction->category?->name ?? 'Uncategorized' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="max-w-xs">
                            <div class="text-sm text-white font-medium truncate">
                                {{ $transaction->description ?? '-' }}
                            </div>
                            @if($transaction->vendor)
                            <div class="text-xs text-gray-300 mt-1">
                                Vendor: {{ $transaction->vendor }}
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold {{ $transaction->type === 'income' ? 'text-green-400' : 'text-red-400' }}">
                            {{ $transaction->type === 'income' ? '+' : '-' }}{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('finance.transactions.show', $transaction) }}" 
                               class="text-blue-300 hover:text-blue-200 transition-colors" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('finance.transactions.edit', $transaction) }}" 
                               class="text-yellow-300 hover:text-yellow-200 transition-colors" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('finance.transactions.destroy', $transaction) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this transaction?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-300 hover:text-red-200 transition-colors" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="bg-white/5 backdrop-blur-sm px-6 py-4 border-t border-white/10">
        <div class="pagination-wrapper">
            {{ $transactions->links() }}
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-16">
        <div class="mx-auto w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">No transactions found</h3>
        <p class="text-gray-300 mb-8 max-w-md mx-auto">Get started by creating a new transaction or using our AI chatbot to analyze your expenses.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <button @click="$dispatch('open-chatbot')" 
                    class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 transition-all duration-200 transform hover:scale-105 shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                Use AI Chatbot
            </button>
            <a href="{{ route('finance.transactions.create') }}" 
               class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition-all duration-200 transform hover:scale-105 border border-white/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Manual Entry
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        window.transactionsTable = function() {
            return {
                sort: { key: 'date', asc: false },
                sortBy(key) {
                    this.sort.asc = this.sort.key === key ? !this.sort.asc : true;
                    this.sort.key = key;
                    this.applySort();
                },
                applySort() {
                    const rows = Array.from(this.$refs.tbody.querySelectorAll('tr'));
                    const idx = { date:0, type:1, category:2, description:3, amount:4 }[this.sort.key];
                    rows.sort((a,b) => {
                        const ta = a.children[idx].innerText.trim();
                        const tb = b.children[idx].innerText.trim();
                        const na = this.sort.key==='amount' ? parseFloat(ta.replace(/[^0-9.-]/g,'')) : ta;
                        const nb = this.sort.key==='amount' ? parseFloat(tb.replace(/[^0-9.-]/g,'')) : tb;
                        if(na<nb) return this.sort.asc?-1:1; if(na>nb) return this.sort.asc?1:-1; return 0;
                    });
                    rows.forEach(r => this.$refs.tbody.appendChild(r));
                },
                matchesSearch(row) {
                    const q = (document.querySelector('input[x-model=clientSearch]')?.value || '').toLowerCase();
                    if(!q) return true;
                    return row.innerText.toLowerCase().includes(q);
                }
            }
        }
    });
</script>
@endsection