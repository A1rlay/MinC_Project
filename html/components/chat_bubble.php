<!-- Chat Bubble Component -->
<div id="chat-bubble" class="fixed bottom-6 right-6 z-40 transition-all duration-300">
    <!-- Chat Bubble Button -->
    <button id="chat-toggle-btn" class="w-14 h-14 bg-gradient-to-br from-[#08415c] to-[#0a5273] text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 flex items-center justify-center relative">
        <i class="fas fa-comment-dots text-xl"></i>
        <span id="chat-unread-badge" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
    </button>
    
    <!-- Chat Window -->
    <div id="chat-window" class="absolute bottom-20 right-0 w-96 h-96 bg-white rounded-lg shadow-2xl hidden flex flex-col transition-all duration-300 transform origin-bottom-right" style="box-shadow: 0 5px 40px rgba(0,0,0,0.16);">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white p-4 rounded-t-lg flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg">MinC Support</h3>
                <p class="text-xs text-white/80">We typically reply within hours</p>
            </div>
            <div class="flex items-center space-x-2">
                <button id="chat-expand-btn" class="text-white hover:bg-white/20 p-2 rounded transition-colors" title="Expand">
                    <i class="fas fa-expand text-sm"></i>
                </button>
                <button id="chat-close-btn" class="text-white hover:bg-white/20 p-2 rounded transition-colors" title="Close">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-3">
            <div class="flex justify-center">
                <div class="bg-white px-3 py-1 rounded-full text-xs text-gray-500">Chat started</div>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="border-t border-gray-200 p-4 bg-white rounded-b-lg">
            <div class="flex gap-2">
                <input 
                    type="text" 
                    id="chat-input" 
                    placeholder="Type your message..." 
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#08415c]/50 text-sm"
                />
                <button 
                    id="chat-send-btn" 
                    class="bg-[#08415c] text-white px-4 py-2 rounded-lg hover:bg-[#0a5273] transition-colors flex items-center justify-center"
                    title="Send message"
                >
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Expanded Chat Modal -->
    <div id="chat-expanded-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-end sm:justify-center p-4 sm:p-0">
        <div class="w-full sm:w-2xl h-screen sm:h-auto sm:max-h-[90vh] bg-white rounded-t-lg sm:rounded-lg shadow-2xl flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white p-4 sm:p-6 flex justify-between items-center rounded-t-lg sm:rounded-t-lg">
                <div>
                    <h2 class="text-2xl font-bold">MinC Support Chat</h2>
                    <p class="text-sm text-white/80">Ask us anything!</p>
                </div>
                <button id="chat-collapse-btn" class="text-white hover:bg-white/20 p-2 rounded transition-colors" title="Collapse">
                    <i class="fas fa-compress text-lg"></i>
                </button>
            </div>
            
            <!-- Messages -->
            <div id="chat-expanded-messages" class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-50 space-y-4">
                <div class="flex justify-center">
                    <div class="bg-white px-4 py-2 rounded-full text-sm text-gray-500">Chat started</div>
                </div>
            </div>
            
            <!-- Input -->
            <div class="border-t border-gray-200 p-4 sm:p-6 bg-white">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="chat-expanded-input" 
                        placeholder="Type your message..." 
                        class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#08415c]/50 text-base"
                    />
                    <button 
                        id="chat-expanded-send-btn" 
                        class="bg-[#08415c] text-white px-6 py-2 rounded-lg hover:bg-[#0a5273] transition-colors flex items-center justify-center"
                    >
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #chat-messages {
        scrollbar-width: thin;
        scrollbar-color: #d1d5db #f9fafb;
    }
    
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #chat-messages::-webkit-scrollbar-track {
        background: #f9fafb;
    }
    
    #chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }
    
    #chat-expanded-messages {
        scrollbar-width: thin;
        scrollbar-color: #d1d5db #f9fafb;
    }
    
    #chat-expanded-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #chat-expanded-messages::-webkit-scrollbar-track {
        background: #f9fafb;
    }
    
    #chat-expanded-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }
    
    .chat-message-customer {
        display: flex;
        justify-content: flex-end;
    }
    
    .chat-message-admin {
        display: flex;
        justify-content: flex-start;
    }
    
    .chat-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 12px;
        word-wrap: break-word;
        animation: slideIn 0.3s ease-out;
    }
    
    .chat-bubble-customer {
        background-color: #08415c;
        color: white;
        border-bottom-right-radius: 4px;
    }
    
    .chat-bubble-admin {
        background-color: #e5e7eb;
        color: #1f2937;
        border-bottom-left-radius: 4px;
    }
    
    .chat-timestamp {
        font-size: 11px;
        opacity: 0.6;
        margin-top: 4px;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatExpandBtn = document.getElementById('chat-expand-btn');
    const chatCollapseBtn = document.getElementById('chat-collapse-btn');
    const chatExpandedModal = document.getElementById('chat-expanded-modal');
    const chatMessages = document.getElementById('chat-messages');
    const chatExpandedMessages = document.getElementById('chat-expanded-messages');
    const chatInput = document.getElementById('chat-input');
    const chatExpandedInput = document.getElementById('chat-expanded-input');
    const chatSendBtn = document.getElementById('chat-send-btn');
    const chatExpandedSendBtn = document.getElementById('chat-expanded-send-btn');
    const chatUnreadBadge = document.getElementById('chat-unread-badge');
    
    let chatOpen = false;
    let sessionId = '';
    
    // Get or create session ID for customer
    function initializeSession() {
        sessionId = localStorage.getItem('chat_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chat_session_id', sessionId);
        }
    }
    
    // Toggle chat window
    chatToggleBtn.addEventListener('click', function() {
        chatOpen = !chatOpen;
        if (chatOpen) {
            chatWindow.classList.remove('hidden');
            chatInput.focus();
            loadMessages();
        } else {
            chatWindow.classList.add('hidden');
        }
    });
    
    // Close chat
    chatCloseBtn.addEventListener('click', function() {
        chatOpen = false;
        chatWindow.classList.add('hidden');
    });
    
    // Expand chat
    chatExpandBtn.addEventListener('click', function() {
        chatWindow.classList.add('hidden');
        chatExpandedModal.classList.remove('hidden');
        loadMessages(true);
        chatExpandedInput.focus();
    });
    
    // Collapse chat
    chatCollapseBtn.addEventListener('click', function() {
        chatExpandedModal.classList.add('hidden');
        chatOpen = true;
        chatWindow.classList.remove('hidden');
        chatInput.focus();
    });
    
    // Close expanded modal when clicking overlay
    chatExpandedModal.addEventListener('click', function(e) {
        if (e.target === chatExpandedModal) {
            chatExpandedModal.classList.add('hidden');
            chatOpen = false;
        }
    });
    
    // Send message from normal chat
    chatSendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    // Send message from expanded chat
    chatExpandedSendBtn.addEventListener('click', function() {
        const message = chatExpandedInput.value.trim();
        if (message) {
            sendMessage(true);
        }
    });
    chatExpandedInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(true);
        }
    });
    
    // Send message function
    function sendMessage(isExpanded = false) {
        const inputElement = isExpanded ? chatExpandedInput : chatInput;
        const message = inputElement.value.trim();
        
        if (!message) return;
        
        // Get customer info from localStorage if available
        const customerName = localStorage.getItem('customer_name') || 'Customer';
        const customerEmail = localStorage.getItem('customer_email') || null;
        
        fetch('./backend/chat/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message_content: message,
                sender_name: customerName,
                sender_email: customerEmail,
                sender_type: 'customer',
                session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                inputElement.value = '';
                addMessageToUI(message, 'customer', isExpanded);
                loadMessages(isExpanded);
            } else {
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error sending message: ' + data.message, 'error', 'Chat Error');
                } else {
                    alert('Error sending message: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showAlertModal === 'function') {
                showAlertModal('Failed to send message. Please try again.', 'error', 'Chat Error');
            } else {
                alert('Failed to send message. Please try again.');
            }
        });
    }
    
    // Add message to UI
    function addMessageToUI(message, sender, isExpanded = false) {
        const container = isExpanded ? chatExpandedMessages : chatMessages;
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message-${sender}`;
        
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageDiv.innerHTML = `
            <div>
                <div class="chat-bubble chat-bubble-${sender}">
                    ${escapeHtml(message)}
                </div>
                <div class="chat-timestamp ${sender === 'customer' ? 'text-right' : 'text-left'}">${time}</div>
            </div>
        `;
        
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    }
    
    // Load messages
    function loadMessages(isExpanded = false) {
        fetch('./backend/chat/send_message.php?type=customer&session_id=' + sessionId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const container = isExpanded ? chatExpandedMessages : chatMessages;
                    
                    // Clear previous messages
                    container.innerHTML = '<div class="flex justify-center"><div class="bg-white px-3 py-1 rounded-full text-xs text-gray-500">Chat started</div></div>';
                    
                    // Add messages
                    data.data.forEach(msg => {
                        const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `chat-message-${msg.sender_type}`;
                        
                        messageDiv.innerHTML = `
                            <div>
                                <div class="chat-bubble chat-bubble-${msg.sender_type}">
                                    <strong class="text-xs block mb-1">${escapeHtml(msg.sender_name)}</strong>
                                    ${escapeHtml(msg.message_content)}
                                </div>
                                <div class="chat-timestamp ${msg.sender_type === 'customer' ? 'text-right' : 'text-left'}">${time}</div>
                            </div>
                        `;
                        
                        container.appendChild(messageDiv);
                    });
                    
                    // Scroll to bottom
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(error => console.error('Error loading messages:', error));
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize
    initializeSession();
    
    // Refresh messages every 3 seconds if chat is open
    setInterval(function() {
        if (chatOpen || !chatExpandedModal.classList.contains('hidden')) {
            loadMessages(chatExpandedModal.classList.contains('hidden') ? false : true);
        }
    }, 3000);
});
</script>
