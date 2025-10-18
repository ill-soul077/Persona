@extends('layouts.app-master')

@section('title', 'View Transaction')
@section('page-icon', '💳')
@section('page-title', 'View Transaction')

@section('action-buttons')
<div class="flex space-x-3">
    <a href="{{ route('finance.transactions.edit', $transaction) }}" 
       class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        <span>Edit</span>
    </a>
    <a href="{{ route('finance.transactions.index') }}" 
       class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to List</span>
    </a>
</div>
@endsection

@section('content')
<!-- Transaction Details Card -->
<div class="glass-card rounded-xl p-8 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-white/10">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Transaction Details</h1>
            <p class="text-gray-300">Created on {{ $transaction->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <div class="text-right">
            @if($transaction->type === 'income')
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                </svg>
                Income
            </span>
            @else
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-500/20 text-red-300 border border-red-500/30">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"></path>
                </svg>
                Expense
            </span>
            @endif
        </div>
    </div>

    <!-- Amount - Large Display -->
    <div class="text-center mb-8">
        <p class="text-gray-300 text-sm font-medium mb-2">Amount</p>
        <p class="text-6xl font-bold {{ $transaction->type === 'income' ? 'text-green-400' : 'text-red-400' }}">
            {{ $transaction->type === 'income' ? '+' : '-' }}{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
        </p>
    </div>

    <!-- Transaction Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Date -->
        <div class="bg-white/5 rounded-xl p-6 border border-white/10">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-300">Date</h3>
            </div>
            <p class="text-white text-lg font-semibold">{{ \Carbon\Carbon::parse($transaction->date)->format('F d, Y') }}</p>
        </div>

        <!-- Category -->
        <div class="bg-white/5 rounded-xl p-6 border border-white/10">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-300">Category</h3>
            </div>
            <p class="text-white text-lg font-semibold">{{ $transaction->category?->name ?? 'Uncategorized' }}</p>
        </div>

        <!-- Currency -->
        <div class="bg-white/5 rounded-xl p-6 border border-white/10">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-300">Currency</h3>
            </div>
            <p class="text-white text-lg font-semibold">{{ $transaction->currency }}</p>
        </div>

        <!-- Status/Type -->
        <div class="bg-white/5 rounded-xl p-6 border border-white/10">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-sm font-medium text-gray-300">Type</h3>
            </div>
            <p class="text-white text-lg font-semibold capitalize">{{ $transaction->type }}</p>
        </div>
    </div>

    <!-- Description -->
    @if($transaction->description)
    <div class="bg-white/5 rounded-xl p-6 border border-white/10 mb-6">
        <div class="flex items-center mb-3">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-300">Description</h3>
        </div>
        <p class="text-white text-base leading-relaxed">{{ $transaction->description }}</p>
    </div>
    @endif

    <!-- Meta Information -->
    @if($transaction->meta && (isset($transaction->meta['vendor']) || isset($transaction->meta['location']) || isset($transaction->meta['tax']) || isset($transaction->meta['tip'])))
    <div class="bg-white/5 rounded-xl p-6 border border-white/10 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Additional Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if(isset($transaction->meta['vendor']))
            <div>
                <p class="text-gray-300 text-sm mb-1">Vendor</p>
                <p class="text-white font-medium">{{ $transaction->meta['vendor'] }}</p>
            </div>
            @endif
            
            @if(isset($transaction->meta['location']))
            <div>
                <p class="text-gray-300 text-sm mb-1">Location</p>
                <p class="text-white font-medium">{{ $transaction->meta['location'] }}</p>
            </div>
            @endif
            
            @if(isset($transaction->meta['tax']))
            <div>
                <p class="text-gray-300 text-sm mb-1">Tax</p>
                <p class="text-white font-medium">{{ $transaction->currency }} {{ number_format($transaction->meta['tax'], 2) }}</p>
            </div>
            @endif
            
            @if(isset($transaction->meta['tip']))
            <div>
                <p class="text-gray-300 text-sm mb-1">Tip</p>
                <p class="text-white font-medium">{{ $transaction->currency }} {{ number_format($transaction->meta['tip'], 2) }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Attachment -->
    @if($transaction->meta && isset($transaction->meta['attachment']))
    <div class="bg-white/5 rounded-xl p-6 border border-white/10 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
            </svg>
            Attachment
        </h3>
        <a href="{{ asset('storage/' . $transaction->meta['attachment']) }}" 
           target="_blank"
           class="inline-flex items-center px-4 py-2 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 rounded-xl transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            View Attachment
        </a>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-6 border-t border-white/10">
        <form method="POST" action="{{ route('finance.transactions.destroy', $transaction) }}" 
              onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="px-6 py-3 bg-red-500/20 hover:bg-red-500/30 border border-red-500/50 rounded-xl text-red-300 font-medium transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <span>Delete Transaction</span>
            </button>
        </form>

        <div class="flex space-x-3">
            <a href="{{ route('finance.transactions.edit', $transaction) }}" 
               class="glass-button text-white px-6 py-3 rounded-xl font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Transaction</span>
            </a>
        </div>
    </div>

    <!-- Metadata Footer -->
    <div class="mt-8 pt-6 border-t border-white/10 flex items-center justify-between text-sm text-gray-400">
        <div>
            <span>Transaction ID: <span class="font-mono text-gray-300">#{{ $transaction->id }}</span></span>
        </div>
        <div>
            <span>Last updated: {{ $transaction->updated_at->diffForHumans() }}</span>
        </div>
    </div>
</div>
@endsection
