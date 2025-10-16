@extends('layouts.app-master')

@section('title', 'AI Chatbot')
@section('page-icon', '🤖')
@section('page-title', 'AI Assistant')

@section('content')
<!-- Page Header -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div>
            <h1 class="text-3xl font-bold text-white">AI Assistant</h1>
            <p class="text-gray-300 mt-2">Chat with your personal AI to manage tasks and finances</p>
        </div>
    </div>
</div>

<!-- Chat Container -->
<div class="glass-card rounded-xl p-6 animate-fade-in">
    <div class="space-y-6">
        <!-- Instructions -->
        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-300 mb-2">💡 How to use the AI Assistant</h3>
            <div class="text-sm text-gray-300 space-y-1">
                <p><strong>Track Expenses:</strong> "spent 25 taka on coffee" or "bought lunch for 150 tk"</p>
                <p><strong>Record Income:</strong> "received 5000 salary" or "earned 1000 from freelance"</p>
                <p><strong>General Chat:</strong> Ask about your spending patterns or financial advice</p>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" class="space-y-4 max-h-96 overflow-y-auto bg-white/5 rounded-lg p-4 backdrop-blur-sm">
            <div class="flex justify-start">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 max-w-sm border border-white/20">
                    <p class="text-sm text-white">
                        Hi! 👋 I'm your Persona AI assistant. I can help you track expenses and income using natural language. Try saying something like "spent 50 taka on lunch" or "received 2000 salary"!
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="flex space-x-2">
            <input
                type="text"
                id="chatInput"
                placeholder="Type your message here..."
                class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all backdrop-blur-sm"
            >
            <button
                id="sendButton"
                class="glass-button px-6 py-3 text-white rounded-xl font-medium flex items-center space-x-2 transition-all"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>Send</span>
            </button>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden flex justify-start">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/20">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-400"></div>
                    <span class="text-sm text-white">AI is thinking...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Confirmation Modal -->
<div id="confirmationModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border border-white/20 w-96 shadow-2xl rounded-xl bg-white/10 backdrop-blur-lg">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-white" id="modalTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-300" id="modalContent">
                    <!-- Content will be populated by JavaScript -->
                </p>
            </div>
            <div class="items-center px-4 py-3 space-x-3">
                <button
                    id="confirmButton"
                    class="px-6 py-2 bg-green-500 text-white text-base font-medium rounded-xl hover:bg-green-600 transition-all transform hover:scale-105"
                >
                    Confirm
                </button>
                <button
                    id="cancelButton"
                    class="px-6 py-2 bg-gray-500 text-white text-base font-medium rounded-xl hover:bg-gray-600 transition-all transform hover:scale-105"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('sendButton');
        const loadingState = document.getElementById('loadingState');
        const confirmationModal = document.getElementById('confirmationModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');

        let pendingConfirmation = null;

        // Send message function
        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            // Add user message to chat
            addMessage(message, 'user');
            chatInput.value = '';

            // Show loading state
            loadingState.classList.remove('hidden');

            // Send to AI
            fetch('{{ route("chatbot.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                loadingState.classList.add('hidden');
                
                if (data.success) {
                    // Add AI response
                    addMessage(data.message, 'ai');

                    // Check if it's a transaction preview
                    if (data.type === 'transaction_preview') {
                        showTransactionPreview(data);
                    }
                    // Check if it's a task preview
                    else if (data.type === 'task_preview') {
                        showTaskPreview(data);
                    }
                } else {
                    addMessage('Sorry, I encountered an error: ' + (data.message || 'Unknown error'), 'ai');
                }
            })
            .catch(error => {
                loadingState.classList.add('hidden');
                addMessage('Sorry, I encountered a network error. Please try again.', 'ai');
                console.error('Error:', error);
            });
        }

        // Add message to chat
        function addMessage(message, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;
            
            const bubbleDiv = document.createElement('div');
            bubbleDiv.className = `rounded-lg px-4 py-2 max-w-xs border ${
                sender === 'user' 
                    ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-blue-400/50' 
                    : 'bg-white/10 backdrop-blur-sm text-white border-white/20'
            }`;
            
            const messageP = document.createElement('p');
            messageP.className = 'text-sm';
            messageP.textContent = message;
            
            bubbleDiv.appendChild(messageP);
            messageDiv.appendChild(bubbleDiv);
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show transaction preview modal
        function showTransactionPreview(data) {
            pendingConfirmation = data;
            
            modalTitle.textContent = 'Confirm Transaction';
            const transaction = data.transaction;
            const amount = parseFloat(transaction.amount).toFixed(2);
            const categoryName = transaction.category ? transaction.category.name : 'Uncategorized';
            
            modalContent.innerHTML = `
                <div class="text-left space-y-2">
                    <p><strong>Type:</strong> ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}</p>
                    <p><strong>Amount:</strong> ৳${amount}</p>
                    <p><strong>Category:</strong> ${categoryName}</p>
                    <p><strong>Description:</strong> ${transaction.description}</p>
                    <p><strong>Date:</strong> ${transaction.date}</p>
                </div>
            `;
            
            confirmationModal.classList.remove('hidden');
        }

        // Show task preview modal
        function showTaskPreview(data) {
            pendingConfirmation = data;
            
            modalTitle.textContent = 'Confirm Task';
            const task = data.task;
            const dueDate = task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date';
            const dueTime = task.due_date ? new Date(task.due_date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
            const tags = task.tags && task.tags.length > 0 ? task.tags.join(', ') : 'None';
            
            modalContent.innerHTML = `
                <div class="text-left space-y-2">
                    <p><strong>Title:</strong> ${task.title}</p>
                    <p><strong>Due Date:</strong> ${dueDate}${dueTime ? ' at ' + dueTime : ''}</p>
                    <p><strong>Priority:</strong> ${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</p>
                    <p><strong>Tags:</strong> ${tags}</p>
                    ${task.description && task.description !== task.title ? `<p><strong>Description:</strong> ${task.description}</p>` : ''}
                </div>
            `;
            
            confirmationModal.classList.remove('hidden');
        }

        // Confirm transaction or task
        confirmButton.addEventListener('click', function() {
            if (!pendingConfirmation) return;

            // Determine if it's a transaction or task
            let url, data;
            if (pendingConfirmation.transaction) {
                url = '{{ route("chatbot.confirm") }}';
                data = pendingConfirmation.transaction;
            } else if (pendingConfirmation.task) {
                url = '{{ route("chatbot.confirm-task") }}';
                data = pendingConfirmation.task;
            } else {
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addMessage('✅ ' + data.message, 'ai');
                } else {
                    addMessage('❌ Error: ' + data.message, 'ai');
                }
                hideConfirmationModal();
            })
            .catch(error => {
                addMessage('❌ Network error occurred while saving.', 'ai');
                hideConfirmationModal();
                console.error('Error:', error);
            });
        });

        // Cancel confirmation
        cancelButton.addEventListener('click', hideConfirmationModal);

        function hideConfirmationModal() {
            confirmationModal.classList.add('hidden');
            pendingConfirmation = null;
        }

        // Event listeners
        sendButton.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Close modal when clicking outside
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                hideConfirmationModal();
            }
        });
</script>
@endsection