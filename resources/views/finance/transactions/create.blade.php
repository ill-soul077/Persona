@extends('layouts.app-master')

@section('title', isset($transaction) ? 'Edit Transaction' : 'Create Transaction')
@section('page-icon', '💰')
@section('page-title', isset($transaction) ? 'Edit Transaction' : 'Create Transaction')

@section('action-buttons')
<a href="{{ route('finance.transactions.index') }}" 
   class="glass-button text-white px-4 py-2 rounded-xl font-medium flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
    <span>Back to List</span>
</a>
@endsection

@section('content')
<div x-data="transactionForm({{ isset($transaction) ? json_encode($transaction) : 'null' }})" x-init="init()">
<!-- Page Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">{{ isset($transaction) ? 'Edit Transaction' : 'Create Transaction' }}</h1>
            <p class="text-gray-300 mt-2">{{ isset($transaction) ? 'Update transaction details' : 'Add a new financial transaction' }}</p>
        </div>
        
        <!-- AI Receipt Scanner Button -->
        @if(!isset($transaction))
        <button type="button" 
                @click="showReceiptScanner = true"
                class="mt-4 md:mt-0 glass-button text-white px-6 py-3 rounded-xl font-medium flex items-center space-x-2 hover:scale-105 transition-transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>📸 Scan Receipt with AI</span>
        </button>
        @endif
    </div>
</div>

<!-- AI Receipt Scanner Modal -->
@if(!isset($transaction))
<div x-show="showReceiptScanner" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm" 
             @click="showReceiptScanner = false"></div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform glass-card rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-7 h-7 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    AI Receipt Scanner
                </h3>
                <button @click="showReceiptScanner = false" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Upload Area -->
            <div x-show="!scanning && !receiptData" class="mb-6">
                <div class="border-2 border-dashed border-white/30 rounded-xl p-8 text-center hover:border-blue-400 transition-colors cursor-pointer"
                     @click="$refs.receiptInput.click()">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-white font-medium mb-2">Click to upload receipt image</p>
                    <p class="text-gray-400 text-sm">Supported: JPG, PNG (Max 5MB)</p>
                    <input type="file" 
                           x-ref="receiptInput"
                           accept="image/jpeg,image/jpg,image/png"
                           @change="handleReceiptUpload"
                           class="hidden">
                </div>
            </div>

            <!-- Scanning Progress -->
            <div x-show="scanning" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-500 border-t-transparent mb-4"></div>
                <p class="text-white font-medium text-lg">🤖 AI is analyzing your receipt...</p>
                <p class="text-gray-400 text-sm mt-2">This may take a few seconds</p>
            </div>

            <!-- Scanned Results -->
            <div x-show="receiptData && !scanning" class="space-y-4">
                <div class="bg-green-500/20 border border-green-500/50 rounded-xl p-4 mb-4">
                    <p class="text-green-300 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Receipt scanned successfully! Review and confirm the details below.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-gray-400 text-sm mb-1">Amount</p>
                        <p class="text-white text-2xl font-bold" x-text="'৳' + (receiptData.amount || 0)"></p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-gray-400 text-sm mb-1">Date</p>
                        <p class="text-white text-lg font-medium" x-text="receiptData.date || 'N/A'"></p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-gray-400 text-sm mb-1">Merchant</p>
                        <p class="text-white text-lg font-medium" x-text="receiptData.merchantName || 'N/A'"></p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-gray-400 text-sm mb-1">Category</p>
                        <p class="text-white text-lg font-medium" x-text="receiptData.category || 'N/A'"></p>
                    </div>
                </div>

                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                    <p class="text-gray-400 text-sm mb-1">Description</p>
                    <p class="text-white" x-text="receiptData.description || 'No description available'"></p>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3 pt-4">
                    <button @click="applyReceiptData" 
                            class="flex-1 glass-button text-white px-6 py-3 rounded-xl font-medium hover:scale-105 transition-transform">
                        ✅ Apply to Form
                    </button>
                    <button @click="resetScanner" 
                            class="flex-1 bg-white/10 text-white px-6 py-3 rounded-xl font-medium hover:bg-white/20 transition-colors">
                        🔄 Scan Another
                    </button>
                </div>
            </div>

            <!-- Error Message -->
            <div x-show="scanError" class="bg-red-500/20 border border-red-500/50 rounded-xl p-4">
                <p class="text-red-300" x-text="scanError"></p>
                <button @click="resetScanner" class="mt-2 text-red-400 hover:text-red-300 text-sm underline">
                    Try again
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Transaction Form -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
        <form method="POST" 
              action="{{ isset($transaction) ? route('finance.transactions.update', $transaction) : route('finance.transactions.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if(isset($transaction))
                @method('PUT')
            @endif

            <!-- Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-200 mb-2">Transaction Type *</label>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" 
                            @click="form.type = 'income'; form.category_id = ''; loadCategories()"
                            :class="form.type === 'income' ? 'border-green-400 bg-green-500/20 text-green-300' : 'border-white/20 text-gray-300 hover:bg-white/10'"
                            class="flex items-center justify-center px-4 py-3 border-2 rounded-xl font-medium transition-all backdrop-blur-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Income
                    </button>
                    
                    <button type="button" 
                            @click="form.type = 'expense'; form.category_id = ''; loadCategories()"
                            :class="form.type === 'expense' ? 'border-red-400 bg-red-500/20 text-red-300' : 'border-white/20 text-gray-300 hover:bg-white/10'"
                            class="flex items-center justify-center px-4 py-3 border-2 rounded-xl font-medium transition-all backdrop-blur-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Expense
                    </button>
                </div>
                <input type="hidden" name="type" x-model="form.type">
                @error('type')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-200 mb-1">Amount *</label>
                    <input type="number" 
                           name="amount" 
                           id="amount" 
                           step="0.01" 
                           min="0.01"
                           x-model="form.amount"
                           required
                           class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('amount') border-red-400 @enderror">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-200 mb-1">Currency *</label>
                    <select name="currency" 
                            id="currency" 
                            x-model="form.currency"
                            required
                            class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('currency') border-red-400 @enderror">
                        <option value="BDT" class="bg-gray-800">BDT (৳)</option>
                        <option value="USD" class="bg-gray-800">USD ($)</option>
                    </select>
                    @error('currency')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-200 mb-1">Date *</label>
                    <input type="date" 
                           name="date" 
                           id="date" 
                           x-model="form.date"
                           :max="new Date().toISOString().split('T')[0]"
                           required
                           class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('date') border-red-400 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-200 mb-1">
                        Category * <span x-text="`(${categories.length} available)`" class="text-xs text-gray-400"></span>
                    </label>
                    <select name="category_id" 
                            id="category_id" 
                            x-model="form.category_id"
                            required
                            class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('category_id') border-red-400 @enderror">
                        <option value="" class="bg-gray-800">Select category...</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" class="bg-gray-800"></option>
                        </template>
                    </select>
                    <input type="hidden" name="category_type" :value="form.type === 'income' ? 'App\\Models\\IncomeSource' : 'App\\Models\\ExpenseCategory'">
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-200 mb-1">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3" 
                          x-model="form.description"
                          placeholder="Optional notes about this transaction..."
                          class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm @error('description') border-red-400 @enderror"></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Metadata (Vendor, Tags, etc) -->
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="vendor" class="block text-sm font-medium text-gray-200 mb-1">Vendor/Source</label>
                    <input type="text" 
                           name="meta[vendor]" 
                           id="vendor" 
                           x-model="form.vendor"
                           placeholder="e.g., Supermarket name, employer..."
                           class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm">
                </div>

                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-200 mb-1">Tags</label>
                    <input type="text" 
                           name="meta[tags]" 
                           id="tags" 
                           x-model="form.tags"
                           placeholder="e.g., personal, business, urgent..."
                           class="block w-full px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm">
                </div>
            </div>

            <!-- Attachment -->
            <div class="mt-6">
                <label for="attachment" class="block text-sm font-medium text-gray-200 mb-1">Receipt/Attachment</label>
                <input type="file" 
                       name="attachment" 
                       id="attachment" 
                       accept="image/*,.pdf"
                       class="block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-white/10 file:text-white hover:file:bg-white/20 transition-all backdrop-blur-sm">
                <p class="mt-1 text-xs text-gray-400">Max 5MB. Supported: JPG, PNG, PDF</p>
                @error('attachment')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex items-center justify-end space-x-3">
                <a href="{{ route('finance.transactions.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-white/20 text-sm font-medium rounded-xl text-gray-300 bg-white/10 hover:bg-white/20 transition-all backdrop-blur-sm">
                    Cancel
                </a>
                
                <button type="submit" 
                        class="glass-button inline-flex items-center px-6 py-3 text-sm font-medium rounded-xl text-white transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ isset($transaction) ? 'Update Transaction' : 'Create Transaction' }}
                </button>
            </div>
        </form>
</div>
</div><!-- End Alpine.js wrapper -->
@endsection

@section('additional-scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

@section('scripts')
<script>
// Pass PHP data to JavaScript
const expenseCategories = @json($expenseCategories ?? []);
const incomeSources = @json($incomeSources ?? []);

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
        showReceiptScanner: false,
        scanning: false,
        receiptData: null,
        scanError: null,
        
        init() {
            this.loadCategories();
        },

        loadCategories() {
            console.log('Loading categories for type:', this.form.type);
            
            if (this.form.type === 'income') {
                this.categories = incomeSources;
                console.log('Loaded income sources:', this.categories);
            } else {
                // Flatten expense categories (include both parents and children)
                this.categories = [];
                expenseCategories.forEach(category => {
                    this.categories.push(category);
                    if (category.children && category.children.length > 0) {
                        category.children.forEach(child => {
                            this.categories.push({
                                id: child.id,
                                name: `${category.name} > ${child.name}`,
                                slug: child.slug
                            });
                        });
                    }
                });
                console.log('Loaded expense categories:', this.categories);
            }
        },

        handleReceiptUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                this.scanError = 'File size must be less than 5MB';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                this.scanError = 'Only JPG and PNG images are supported';
                return;
            }

            this.scanReceipt(file);
        },

        async scanReceipt(file) {
            this.scanning = true;
            this.scanError = null;
            this.receiptData = null;

            const formData = new FormData();
            formData.append('receipt_image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const response = await fetch('{{ route("finance.transactions.scan-receipt") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    // Show debug message if available (only in debug mode)
                    const errorMsg = result.debug_message || result.error || 'Failed to scan receipt';
                    throw new Error(errorMsg);
                }

                this.receiptData = result.data;
                console.log('Receipt scanned:', this.receiptData);

            } catch (error) {
                console.error('Receipt scan error:', error);
                this.scanError = error.message || 'Failed to scan receipt. Please try again.';
            } finally {
                this.scanning = false;
            }
        },

        applyReceiptData() {
            if (!this.receiptData) return;

            // Set form type to expense (receipts are always expenses)
            this.form.type = 'expense';
            this.form.amount = this.receiptData.amount || '';
            this.form.date = this.receiptData.date || new Date().toISOString().split('T')[0];
            this.form.description = this.receiptData.description || '';
            this.form.vendor = this.receiptData.merchantName || '';

            // Load expense categories
            this.loadCategories();

            // Try to match category by slug
            if (this.receiptData.category) {
                const matchedCategory = this.categories.find(cat => {
                    const catSlug = (cat.slug || cat.name).toLowerCase().replace(/\s+/g, '-');
                    const receiptCat = this.receiptData.category.toLowerCase().replace(/\s+/g, '-');
                    return catSlug.includes(receiptCat) || receiptCat.includes(catSlug);
                });
                
                if (matchedCategory) {
                    this.form.category_id = matchedCategory.id;
                }
            }

            // Update the form fields
            document.getElementById('amount').value = this.form.amount;
            document.getElementById('date').value = this.form.date;
            document.getElementById('description').value = this.form.description;
            document.getElementById('vendor').value = this.form.vendor;

            // Close modal
            this.showReceiptScanner = false;
            
            // Show success message
            alert('✅ Receipt data applied to the form! Please review and submit.');
        },

        resetScanner() {
            this.receiptData = null;
            this.scanError = null;
            this.scanning = false;
            this.$refs.receiptInput.value = '';
        }
    };
}
</script>
@endsection
