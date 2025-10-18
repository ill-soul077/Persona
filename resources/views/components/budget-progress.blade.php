<!-- Budget Progress Card -->
@if($budgetData)
<div class="glass-card rounded-xl p-6 animate-fade-in" x-data="budgetWidget({{ json_encode($budgetData) }})">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-white">Monthly Budget</h3>
            <p class="text-gray-300 text-sm">{{ now()->format('F Y') }}</p>
        </div>
        <button @click="showEditModal = true" class="text-blue-400 hover:text-blue-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </button>
    </div>

    <!-- Budget Amount -->
    <div class="mb-4">
        <div class="flex justify-between items-baseline mb-2">
            <span class="text-2xl font-bold text-white">${{ number_format($budgetData['spent'], 2) }}</span>
            <span class="text-gray-300">of ${{ number_format($budgetData['amount'], 2) }}</span>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
            <div 
                class="h-full rounded-full transition-all duration-500 {{ $budgetData['is_exceeded'] ? 'bg-red-500' : ($budgetData['percentage'] >= 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                style="width: {{ min($budgetData['percentage'], 100) }}%"
            ></div>
        </div>
        
        <div class="flex justify-between items-center mt-2">
            <span class="text-sm {{ $budgetData['is_exceeded'] ? 'text-red-400' : ($budgetData['percentage'] >= 80 ? 'text-yellow-400' : 'text-green-400') }}">
                {{ $budgetData['percentage'] }}% used
            </span>
            <span class="text-sm text-gray-300">
                ${{ number_format($budgetData['remaining'], 2) }} remaining
            </span>
        </div>
    </div>

    <!-- Status Message -->
    @if($budgetData['is_exceeded'])
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-3 flex items-start space-x-2">
            <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm text-red-300">
                <strong>Over Budget!</strong> You've exceeded your budget by ${{ number_format(abs($budgetData['remaining']), 2) }}.
            </div>
        </div>
    @elseif($budgetData['percentage'] >= 80)
        <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-3 flex items-start space-x-2">
            <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-yellow-300">
                <strong>Near Limit!</strong> You're approaching your budget limit. Spend carefully.
            </div>
        </div>
    @else
        <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-3 flex items-start space-x-2">
            <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-green-300">
                <strong>On Track!</strong> You're managing your budget well. Keep it up!
            </div>
        </div>
    @endif

    <!-- Edit Budget Modal -->
    <div x-show="showEditModal" 
         x-cloak
         @click.self="showEditModal = false"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="glass-card rounded-xl p-6 max-w-md w-full" @click.stop>
            <h3 class="text-xl font-bold text-white mb-4">Edit Monthly Budget</h3>
            
            <form @submit.prevent="saveBudget">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Budget Amount</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            x-model="editAmount"
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-400"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Notes (optional)</label>
                        <textarea 
                            x-model="editNotes"
                            rows="3"
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-400"
                            placeholder="Budget goals or notes..."
                        ></textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input 
                            type="checkbox" 
                            id="applyFuture" 
                            x-model="applyToFuture"
                            class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
                        >
                        <label for="applyFuture" class="text-gray-300 text-sm">
                            Apply to next 12 months
                        </label>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button 
                        type="button"
                        @click="showEditModal = false"
                        class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
                    >
                        <span x-show="!saving">Save</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function budgetWidget(initialData) {
    return {
        showEditModal: false,
        editAmount: initialData.amount,
        editNotes: '',
        applyToFuture: false,
        saving: false,

        async saveBudget() {
            this.saving = true;
            
            try {
                const response = await fetch('{{ route("finance.budget.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        month: '{{ now()->format("Y-m") }}',
                        amount: this.editAmount,
                        notes: this.editNotes,
                        apply_to_future: this.applyToFuture,
                        currency: 'USD'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showEditModal = false;
                    window.location.reload(); // Reload to show updated budget
                } else {
                    alert('Failed to save budget');
                }
            } catch (error) {
                console.error('Error saving budget:', error);
                alert('An error occurred while saving');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>

@else
<!-- No Budget Set - Prompt to Create -->
<div class="glass-card rounded-xl p-6 animate-fade-in" x-data="{ showCreateModal: false }">
    <div class="text-center py-8">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-white mb-2">No Budget Set</h3>
        <p class="text-gray-400 mb-4">Set a monthly budget to track your spending</p>
        <button 
            @click="showCreateModal = true"
            class="glass-button text-white px-6 py-2 rounded-xl font-medium"
        >
            Set Budget
        </button>
    </div>

    <!-- Create Budget Modal (same as edit modal above) -->
    <div x-show="showCreateModal" 
         x-cloak
         @click.self="showCreateModal = false"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="glass-card rounded-xl p-6 max-w-md w-full" @click.stop x-data="createBudgetForm()">
            <h3 class="text-xl font-bold text-white mb-4">Set Monthly Budget</h3>
            
            <form @submit.prevent="createBudget">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Budget Amount</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            x-model="amount"
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-400"
                            placeholder="Enter amount..."
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-gray-300 text-sm font-medium mb-2">Notes (optional)</label>
                        <textarea 
                            x-model="notes"
                            rows="3"
                            class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-blue-400"
                            placeholder="Budget goals or notes..."
                        ></textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input 
                            type="checkbox" 
                            id="applyFutureCreate" 
                            x-model="applyToFuture"
                            class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
                        >
                        <label for="applyFutureCreate" class="text-gray-300 text-sm">
                            Apply to next 12 months
                        </label>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button 
                        type="button"
                        @click="showCreateModal = false"
                        class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
                    >
                        <span x-show="!saving">Create Budget</span>
                        <span x-show="saving">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function createBudgetForm() {
    return {
        amount: '',
        notes: '',
        applyToFuture: false,
        saving: false,

        async createBudget() {
            this.saving = true;
            
            try {
                const response = await fetch('{{ route("finance.budget.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        month: '{{ now()->format("Y-m") }}',
                        amount: this.amount,
                        notes: this.notes,
                        apply_to_future: this.applyToFuture,
                        currency: 'USD'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload(); // Reload to show new budget
                } else {
                    alert('Failed to create budget');
                }
            } catch (error) {
                console.error('Error creating budget:', error);
                alert('An error occurred while creating budget');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endif
