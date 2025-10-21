<!-- Chatbot Widget Component -->
<div x-data="chatbot()" 
     @open-chatbot.window="open()"
     @keydown.escape.window="close()"
     class="fixed bottom-0 right-0 z-50 mb-4 mr-4">
    
    <!-- Chat Button (when closed) -->
    <button @click="toggle()" 
            x-show="!isOpen"
            class="relative inline-flex items-center justify-center w-14 h-14 rounded-full bg-purple-600 text-white shadow-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        <span x-show="unreadCount > 0" 
              class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
              x-text="unreadCount"></span>
    </button>

    <!-- Chat Window -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="flex flex-col bg-white rounded-lg shadow-2xl w-96 h-[600px]"
         x-cloak>
        
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 bg-purple-600 text-white rounded-t-lg">
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span x-show="isTyping" class="absolute bottom-0 right-0 block h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
                </div>
                <div>
                    <h3 class="font-semibold">Finance Assistant</h3>
                    <p class="text-xs opacity-90" x-text="isTyping ? 'Typing...' : 'Online'"></p>
                </div>
            </div>
            <button @click="close()" class="text-white hover:text-purple-100 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50" x-ref="messagesContainer">
            <!-- Welcome Message -->
            <div x-show="messages.length === 0" class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Welcome to Finance Assistant!</h4>
                <p class="text-sm text-gray-600 mb-4">I can help you track your expenses and income.</p>
                <div class="text-xs text-left bg-white rounded-lg p-3 mx-4 shadow-sm">
                    <p class="font-medium text-gray-700 mb-2">Try saying:</p>
                    <ul class="space-y-1 text-gray-600">
                        <li>• "I spent 500 taka on groceries"</li>
                        <li>• "Received salary of 50000 BDT"</li>
                        <li>• "Paid 1200 for electricity bill"</li>
                    </ul>
                </div>
            </div>

            <!-- Message List -->
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.sender === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="message.sender === 'user' ? 'bg-purple-600 text-white' : 'bg-white text-gray-900'" 
                         class="max-w-[80%] rounded-lg px-4 py-2 shadow-sm">
                        <div x-html="formatMessage(message.text)"></div>
                        <div class="text-xs opacity-75 mt-1" x-text="message.time"></div>
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white text-gray-900 rounded-lg px-4 py-3 shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 p-4 bg-white rounded-b-lg">
            <form @submit.prevent="sendMessage()" class="flex items-end space-x-2">
                <div class="flex-1">
                    <textarea x-model="inputText"
                              @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                              :disabled="isTyping"
                              placeholder="Type your message..."
                              rows="1"
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm resize-none"
                              style="max-height: 120px;"
                              x-ref="input"></textarea>
                </div>
                <button type="submit" 
                        :disabled="!inputText.trim() || isTyping"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <div class="text-xs text-gray-500 mt-2">
                Press Enter to send, Shift+Enter for new line
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmation" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="cancelConfirmation()"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Transaction<span x-show="parsedTransactions.length>1">s</span></h3>
                        <div class="mt-4 space-y-3" x-show="parsedTransactions && parsedTransactions.length">
                            <template x-for="(t, i) in parsedTransactions" :key="i">
                                <div class="bg-gray-50 rounded-md p-3 border" :class="selected[i] ? 'border-purple-300' : 'border-gray-200'">
                                    <div class="flex items-start justify-between">
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div class="text-gray-500">Type:</div>
                                            <div class="font-medium capitalize" :class="t.type === 'income' ? 'text-green-600' : 'text-red-600'" x-text="t.type"></div>

                                            <div class="text-gray-500">Amount:</div>
                                            <div class="font-medium" x-text="`${t.currency} ${t.amount}`"></div>

                                            <div class="text-gray-500">Category:</div>
                                            <div class="font-medium" x-text="t.category"></div>

                                            <div class="text-gray-500">Date:</div>
                                            <div class="font-medium" x-text="t.date"></div>

                                            <template x-if="t.description">
                                                <div class="text-gray-500">Description:</div>
                                                <div class="font-medium" x-text="t.description"></div>
                                            </template>
                                        </div>
                                        <label class="ml-4 inline-flex items-center space-x-2 cursor-pointer">
                                            <input type="checkbox" class="rounded text-purple-600 focus:ring-purple-500" x-model="selected[i]"><span class="text-sm text-gray-700">Save</span>
                                        </label>
                                    </div>

                                    <div x-show="t.confidence < 0.8" class="mt-2 flex items-start space-x-2 text-xs text-orange-600 bg-orange-50 rounded-md p-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>Low confidence. Please review this item carefully.</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            @click="confirmTransaction()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm & Save Selected
                    </button>
                    <button type="button" 
                            @click="cancelConfirmation()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatbot() {
    return {
        isOpen: false,
        isTyping: false,
        inputText: '',
        messages: [],
        unreadCount: 0,
        showConfirmation: false,
    parsedData: null,
    parsedTransactions: [],
    selected: [],

        init() {
            // Load messages from localStorage
            const saved = localStorage.getItem('chatMessages');
            if (saved) {
                this.messages = JSON.parse(saved);
            }
        },

        open() {
            this.isOpen = true;
            this.unreadCount = 0;
            this.$nextTick(() => {
                this.$refs.input.focus();
                this.scrollToBottom();
            });
        },

        close() {
            this.isOpen = false;
        },

        toggle() {
            this.isOpen ? this.close() : this.open();
        },

        async sendMessage() {
            const text = this.inputText.trim();
            if (!text) return;

            // Add user message
            this.addMessage('user', text);
            this.inputText = '';
            this.isTyping = true;

            try {
                // Call parse API
                const response = await fetch('/api/chat/parse-finance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text })
                });

                const data = await response.json();

                if (data.success) {
                    // Normalize to array
                    this.parsedTransactions = (data.data?.transactions || []);
                    this.parsedData = { ai_log_id: data.data?.ai_log_id, requires_confirmation: data.data?.requires_confirmation };
                    this.selected = this.parsedTransactions.map(() => true);
                    this.isTyping = false;

                    // Summary message
                    const count = this.parsedTransactions.length;
                    const preview = this.parsedTransactions.map(t => `${t.description || t.category} ${t.currency} ${t.amount}`).join(', ');
                    const confirmMsg = count > 1
                        ? `I found ${count} transactions: ${preview}. Review and confirm which ones to save.`
                        : `I found a ${this.parsedTransactions[0]?.type} of ${this.parsedTransactions[0]?.currency} ${this.parsedTransactions[0]?.amount} for ${this.parsedTransactions[0]?.category}. Save it?`;
                    this.addMessage('bot', confirmMsg);
                    this.showConfirmation = true;
                } else {
                    this.isTyping = false;
                    this.addMessage('bot', data.message || 'Sorry, I couldn\'t understand that. Please try rephrasing.');
                }
            } catch (error) {
                this.isTyping = false;
                this.addMessage('bot', 'Sorry, something went wrong. Please try again.');
                console.error('Chat error:', error);
            }
        },

        async confirmTransaction() {
            this.showConfirmation = false;
            this.isTyping = true;

            try {
                // Collect selected items
                const items = this.parsedTransactions
                    .map((t, i) => ({ t, i }))
                    .filter(x => this.selected[x.i])
                    .map(x => ({ ...x.t, ai_log_id: this.parsedData?.ai_log_id }));

                if (items.length === 0) {
                    this.isTyping = false;
                    this.addMessage('bot', 'No items selected to save.');
                    return;
                }

                const payload = items.length === 1
                    ? { ...items[0] }
                    : { ai_log_id: this.parsedData?.ai_log_id, transactions: items };

                const response = await fetch('/api/chat/confirm-transaction', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    const savedCount = (data.saved?.length) || 1;
                    this.addMessage('bot', `✅ Saved ${savedCount} transaction${savedCount>1?'s':''} successfully!`);
                    window.showToast('Transactions saved!', 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    this.addMessage('bot', '❌ Failed to save: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                this.addMessage('bot', '❌ Error saving transaction(s). Please try again.');
                console.error('Save error:', error);
            } finally {
                this.isTyping = false;
                this.parsedData = null;
                this.parsedTransactions = [];
                this.selected = [];
            }
        },

        cancelConfirmation() {
            this.showConfirmation = false;
            this.parsedData = null;
            this.addMessage('bot', 'Transaction cancelled. Feel free to try again!');
        },

        addMessage(sender, text) {
            const message = {
                sender,
                text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            };
            
            this.messages.push(message);
            
            // Save to localStorage
            localStorage.setItem('chatMessages', JSON.stringify(this.messages.slice(-50))); // Keep last 50
            
            if (!this.isOpen && sender === 'bot') {
                this.unreadCount++;
            }
            
            this.$nextTick(() => this.scrollToBottom());
        },

        formatMessage(text) {
            // Highlight entities: amounts, categories, vendors
            return text
                .replace(/(\d+(?:,\d{3})*(?:\.\d{2})?)\s*(BDT|USD|taka|dollars?)/gi, '<span class="font-semibold text-green-600">$1 $2</span>')
                .replace(/(groceries|rent|salary|food|transport|utilities|entertainment)/gi, '<span class="font-semibold text-blue-600">$1</span>');
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
    };
}
</script>
@endpush
