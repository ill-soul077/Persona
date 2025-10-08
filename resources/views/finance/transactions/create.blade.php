@extends('layouts.app')

@section('title', isset($transaction) ? 'Edit Transaction' : 'Create Transaction')

@section('header')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">{{ isset($transaction) ? 'Edit Transaction' : 'Create Transaction' }}</h1>
    <a href="{{ route('finance.transactions.index') }}" 
       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to List
    </a>
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" 
              action="{{ isset($transaction) ? route('finance.transactions.update', $transaction) : route('finance.transactions.store') }}"
              enctype="multipart/form-data"
              x-data="transactionForm({{ isset($transaction) ? json_encode($transaction) : 'null' }})">
            @csrf
            @if(isset($transaction))
                @method('PUT')
            @endif

            <!-- Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" 
                            @click="form.type = 'income'; loadCategories()"
                            :class="form.type === 'income' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                            class="flex items-center justify-center px-4 py-3 border-2 rounded-lg font-medium transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Income
                    </button>
                    
                    <button type="button" 
                            @click="form.type = 'expense'; loadCategories()"
                            :class="form.type === 'expense' ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                            class="flex items-center justify-center px-4 py-3 border-2 rounded-lg font-medium transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Expense
                    </button>
                </div>
                <input type="hidden" name="type" x-model="form.type">
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                    <input type="number" 
                           name="amount" 
                           id="amount" 
                           step="0.01" 
                           min="0.01"
                           x-model="form.amount"
                           required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('amount') border-red-300 @enderror">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
                    <select name="currency" 
                            id="currency" 
                            x-model="form.currency"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('currency') border-red-300 @enderror">
                        <option value="BDT">BDT (৳)</option>
                        <option value="USD">USD ($)</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" 
                           name="date" 
                           id="date" 
                           x-model="form.date"
                           :max="new Date().toISOString().split('T')[0]"
                           required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('date') border-red-300 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" 
                            id="category_id" 
                            x-model="form.category_id"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('category_id') border-red-300 @enderror">
                        <option value="">Select category...</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :selected="form.category_id == cat.id"></option>
                        </template>
                    </select>
                    <input type="hidden" name="category_type" :value="form.type === 'income' ? 'App\\Models\\IncomeSource' : 'App\\Models\\ExpenseCategory'">
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3" 
                          x-model="form.description"
                          placeholder="Optional notes about this transaction..."
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror"></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Metadata (Vendor, Tags, etc) -->
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="vendor" class="block text-sm font-medium text-gray-700 mb-1">Vendor/Source</label>
                    <input type="text" 
                           name="meta[vendor]" 
                           id="vendor" 
                           x-model="form.vendor"
                           placeholder="e.g., Supermarket name, employer..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                    <input type="text" 
                           name="meta[tags]" 
                           id="tags" 
                           x-model="form.tags"
                           placeholder="e.g., personal, business, urgent..."
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <!-- Attachment -->
            <div class="mt-6">
                <label for="attachment" class="block text-sm font-medium text-gray-700 mb-1">Receipt/Attachment</label>
                <input type="file" 
                       name="attachment" 
                       id="attachment" 
                       accept="image/*,.pdf"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="mt-1 text-xs text-gray-500">Max 5MB. Supported: JPG, PNG, PDF</p>
                @error('attachment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center justify-end space-x-3">
                <a href="{{ route('finance.transactions.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ isset($transaction) ? 'Update Transaction' : 'Create Transaction' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function transactionForm(existing = null) {
    return {
        form: {
            type: existing?.type || 'expense',
            amount: existing?.amount || '',
            currency: existing?.currency || 'BDT',
            date: existing?.date || new Date().toISOString().split('T')[0],
            category_id: existing?.category_id || '',
            description: existing?.description || '',
            vendor: existing?.meta?.vendor || '',
            tags: existing?.meta?.tags || ''
        },
        categories: [],
        
        async init() {
            await this.loadCategories();
        },

        async loadCategories() {
            const endpoint = this.form.type === 'income' 
                ? '/api/income-sources'
                : '/api/expense-categories';
            
            try {
                const response = await fetch(endpoint);
                const data = await response.json();
                this.categories = data.data || data;
            } catch (error) {
                console.error('Failed to load categories:', error);
                this.categories = [];
            }
        }
    };
}
</script>
@endpush
