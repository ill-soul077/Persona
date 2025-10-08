<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AI Chatbot') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Instructions -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">💡 How to use the AI Chatbot</h3>
                            <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                <p><strong>For Finances:</strong> "Add expense of $25 for lunch" or "I earned $500 from freelancing"</p>
                                <p><strong>For Tasks:</strong> "Remind me to call John tomorrow at 3pm" or "Add task to finish report by Friday"</p>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div id="chatMessages" class="space-y-4 max-h-96 overflow-y-auto">
                            <div class="flex justify-start">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 max-w-xs">
                                    <p class="text-sm text-gray-900 dark:text-gray-100">
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
                                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                            >
                            <button
                                id="sendButton"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Send
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div id="loadingState" class="hidden flex justify-start">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2">
                                <div class="flex items-center space-x-2">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                                    <span class="text-sm text-gray-900 dark:text-gray-100">AI is thinking...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="modalTitle">Confirm Action</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="modalContent">
                        <!-- Content will be populated by JavaScript -->
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button
                        id="confirmButton"
                        class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300"
                    >
                        Confirm
                    </button>
                    <button
                        id="cancelButton"
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

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
            bubbleDiv.className = `rounded-lg px-4 py-2 max-w-xs ${
                sender === 'user' 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
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
</x-app-layout>