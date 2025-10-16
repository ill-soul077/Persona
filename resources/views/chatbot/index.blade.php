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
            <h3 class="text-sm font-medium text-blue-300 mb-2">💡 How to use the AI Chatbot</h3>
            <div class="text-sm text-gray-300 space-y-1">
                <p><strong>For Finances:</strong> "Add expense of $25 for lunch" or "I earned $500 from freelancing"</p>
                <p><strong>For Tasks:</strong> "Remind me to call John tomorrow at 3pm" or "Add task to finish report by Friday"</p>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chatMessages" class="space-y-4 max-h-96 overflow-y-auto bg-white/5 rounded-lg p-4 backdrop-blur-sm">
            <div class="flex justify-start">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2 max-w-xs border border-white/20">
                    <p class="text-sm text-white">
                        Hi! I'm your AI assistant. I can help you add transactions and tasks using natural language. What would you like to do?
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
            fetch('{{ route("chat.send") }}', {
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
                    addMessage(data.response, 'ai');

                    // Check if confirmation is needed
                    if (data.requiresConfirmation) {
                        showConfirmationModal(data);
                    }
                } else {
                    addMessage('Sorry, I encountered an error: ' + (data.error || 'Unknown error'), 'ai');
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

        // Show confirmation modal
        function showConfirmationModal(data) {
            pendingConfirmation = data;
            
            if (data.type === 'transaction') {
                modalTitle.textContent = 'Confirm Transaction';
                modalContent.innerHTML = `
                    <strong>Type:</strong> ${data.parsedData.type}<br>
                    <strong>Amount:</strong> $${data.parsedData.amount}<br>
                    <strong>Description:</strong> ${data.parsedData.description}<br>
                    <strong>Category:</strong> ${data.parsedData.category || 'N/A'}<br>
                    <strong>Date:</strong> ${data.parsedData.date || 'Today'}
                `;
            } else if (data.type === 'task') {
                modalTitle.textContent = 'Confirm Task';
                modalContent.innerHTML = `
                    <strong>Title:</strong> ${data.parsedData.title}<br>
                    <strong>Description:</strong> ${data.parsedData.description || 'N/A'}<br>
                    <strong>Due Date:</strong> ${data.parsedData.due_date || 'Not specified'}<br>
                    <strong>Priority:</strong> ${data.parsedData.priority || 'Medium'}
                `;
            }
            
            confirmationModal.classList.remove('hidden');
        }

        // Confirm action
        confirmButton.addEventListener('click', function() {
            if (!pendingConfirmation) return;

            const endpoint = pendingConfirmation.type === 'transaction' 
                ? '{{ route("chat.confirm.transaction") }}'
                : '{{ route("chat.confirm.task") }}';

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(pendingConfirmation.parsedData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addMessage(`✅ ${pendingConfirmation.type === 'transaction' ? 'Transaction' : 'Task'} created successfully!`, 'ai');
                } else {
                    addMessage(`❌ Error creating ${pendingConfirmation.type}: ${data.error}`, 'ai');
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