<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$chatIdentity = 'guest';
$chatUserId = null;
$chatDefaultName = 'Customer';
$chatDefaultEmail = null;

if (isset($_SESSION['user_id'])) {
    $chatUserId = (int)$_SESSION['user_id'];
    $chatIdentity = 'user_' . $chatUserId;

    $sessionFullName = trim((string)($_SESSION['full_name'] ?? ''));
    if ($sessionFullName === '') {
        $sessionFullName = trim((string)(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '')));
    }
    if ($sessionFullName !== '') {
        $chatDefaultName = $sessionFullName;
    }

    $sessionEmail = trim((string)($_SESSION['email'] ?? ''));
    if ($sessionEmail !== '' && filter_var($sessionEmail, FILTER_VALIDATE_EMAIL)) {
        $chatDefaultEmail = $sessionEmail;
    }
}
?>

<div id="chat-bubble" class="fixed bottom-6 right-6 z-[60]">
    <button id="chat-toggle-btn" class="w-14 h-14 bg-gradient-to-br from-[#08415c] to-[#0a5273] text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 flex items-center justify-center relative" title="Open support chat">
        <i class="fas fa-comment-dots text-xl"></i>
        <span id="chat-unread-badge" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
    </button>
</div>

<div id="chat-overlay" class="hidden fixed inset-0 bg-black/45 z-[9998]"></div>

<section id="chat-window" class="hidden fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-[9999] w-[80vw] max-w-[1200px] h-[80vh] min-h-[520px] bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden" aria-label="Support chat">
    <header class="bg-gradient-to-r from-[#08415c] to-[#0a5273] text-white px-6 py-4 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-2xl leading-tight">MinC Support Chat</h3>
            <p class="text-sm text-white/85">Ask us anything</p>
        </div>
        <button id="chat-close-btn" class="w-10 h-10 rounded-lg hover:bg-white/20 transition-colors" title="Close chat" aria-label="Close chat">
            <i class="fas fa-times text-lg"></i>
        </button>
    </header>

    <div id="chat-messages" class="flex-1 overflow-y-auto p-5 bg-gray-50"></div>

    <footer class="border-t border-gray-200 p-4 bg-white">
        <div class="flex gap-2">
            <input
                type="text"
                id="chat-input"
                placeholder="Type your message..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#08415c]/45 text-base"
                maxlength="1200"
            />
            <button
                id="chat-send-btn"
                class="bg-[#08415c] text-white px-5 py-3 rounded-lg hover:bg-[#0a5273] transition-colors flex items-center justify-center"
                title="Send message"
                aria-label="Send message"
            >
                <i class="fas fa-paper-plane text-sm"></i>
            </button>
        </div>
    </footer>
</section>

<style>
    #chat-messages {
        scrollbar-width: thin;
        scrollbar-color: #d1d5db #f9fafb;
    }

    #chat-messages::-webkit-scrollbar {
        width: 8px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: #f9fafb;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 999px;
    }

    .chat-start-pill {
        display: inline-block;
        margin: 0 auto 14px auto;
        background: #fff;
        color: #6b7280;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 0.28rem 0.72rem;
        font-size: 0.74rem;
    }

    .chat-message-row {
        display: flex;
        width: 100%;
        margin-bottom: 12px;
        animation: slideIn 0.2s ease-out;
    }

    .chat-message-customer {
        justify-content: flex-end;
    }

    .chat-message-admin {
        justify-content: flex-start;
    }

    .chat-message-wrap {
        display: flex;
        flex-direction: column;
        max-width: min(74%, 760px);
    }

    .chat-message-customer .chat-message-wrap {
        align-items: flex-end;
    }

    .chat-message-admin .chat-message-wrap {
        align-items: flex-start;
    }

    .chat-sender {
        font-size: 0.74rem;
        margin-bottom: 2px;
        opacity: 0.75;
    }

    .chat-bubble {
        padding: 11px 14px;
        border-radius: 12px;
        max-width: 100%;
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.4;
    }

    .chat-bubble-customer {
        background: #08415c;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .chat-bubble-admin {
        background: #e5e7eb;
        color: #1f2937;
        border-bottom-left-radius: 4px;
    }

    .chat-timestamp {
        font-size: 0.68rem;
        color: #6b7280;
        margin-top: 4px;
    }

    @media (max-width: 1024px) {
        #chat-window {
            width: calc(100vw - 1.5rem);
            height: 82vh;
            min-height: 460px;
        }

        .chat-message-wrap {
            max-width: 88%;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatIdentity = <?php echo json_encode($chatIdentity); ?>;
    const sessionUserId = <?php echo json_encode($chatUserId); ?>;
    const sessionDefaultName = <?php echo json_encode($chatDefaultName); ?>;
    const sessionDefaultEmail = <?php echo json_encode($chatDefaultEmail); ?>;
    const sessionStorageKey = 'chat_session_id_' + String(chatIdentity || 'guest');
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatOverlay = document.getElementById('chat-overlay');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSendBtn = document.getElementById('chat-send-btn');

    let chatOpen = false;
    let sessionId = '';

    function initializeSession() {
        sessionId = localStorage.getItem(sessionStorageKey);
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
            localStorage.setItem(sessionStorageKey, sessionId);
        }
    }

    function openChat() {
        chatOpen = true;
        chatOverlay.classList.remove('hidden');
        chatWindow.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        loadMessages();
        chatInput.focus();
    }

    function closeChat() {
        chatOpen = false;
        chatOverlay.classList.add('hidden');
        chatWindow.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = String(text || '');
        return div.innerHTML;
    }

    function formatTime(dateValue) {
        if (!dateValue) return '';
        const date = new Date(dateValue);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderMessages(messages) {
        chatMessages.innerHTML = '';
        const startWrap = document.createElement('div');
        startWrap.className = 'flex justify-center';
        startWrap.innerHTML = '<span class="chat-start-pill">Chat started</span>';
        chatMessages.appendChild(startWrap);

        messages.forEach((msg) => {
            const senderType = msg.sender_type === 'admin' ? 'admin' : 'customer';
            const safeName = senderType === 'customer' ? 'You' : (msg.sender_name || 'Support');
            const time = formatTime(msg.created_at);

            const row = document.createElement('div');
            row.className = 'chat-message-row chat-message-' + senderType;
            row.innerHTML = `
                <div class="chat-message-wrap">
                    <span class="chat-sender">${escapeHtml(safeName)}</span>
                    <div class="chat-bubble chat-bubble-${senderType}">${escapeHtml(msg.message_content)}</div>
                    <span class="chat-timestamp">${escapeHtml(time)}</span>
                </div>
            `;
            chatMessages.appendChild(row);
        });

        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function loadMessages() {
        if (!sessionId) return;
        fetch('./backend/chat/send_message.php?type=customer&session_id=' + encodeURIComponent(sessionId))
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success' && Array.isArray(data.data)) {
                    renderMessages(data.data);
                }
            })
            .catch((error) => {
                console.error('Error loading messages:', error);
            });
    }

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message || !sessionId) return;

        const isGenericName = (name) => /^(customer|anonymous customer|anonymous|guest|user|unknown)$/i.test(String(name || '').trim());

        const storedName = String(localStorage.getItem('customer_name') || '').trim();
        const storedEmail = String(localStorage.getItem('customer_email') || '').trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        const customerName = (storedName !== '' && !isGenericName(storedName))
            ? storedName
            : String(sessionDefaultName || 'Customer');

        const customerEmail = (storedEmail !== '' && emailPattern.test(storedEmail))
            ? storedEmail
            : (sessionDefaultEmail || null);

        chatSendBtn.disabled = true;
        fetch('./backend/chat/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message_content: message,
                sender_name: customerName,
                sender_email: customerEmail,
                sender_id: sessionUserId ? Number(sessionUserId) : null,
                sender_type: 'customer',
                session_id: sessionId
            })
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === 'success') {
                    chatInput.value = '';
                    loadMessages();
                    return;
                }
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Error sending message: ' + (data.message || 'Unknown error'), 'error', 'Chat Error');
                    return;
                }
                alert('Error sending message: ' + (data.message || 'Unknown error'));
            })
            .catch((error) => {
                console.error('Error sending message:', error);
                if (typeof showAlertModal === 'function') {
                    showAlertModal('Failed to send message. Please try again.', 'error', 'Chat Error');
                    return;
                }
                alert('Failed to send message. Please try again.');
            })
            .finally(() => {
                chatSendBtn.disabled = false;
            });
    }

    chatToggleBtn.addEventListener('click', function() {
        if (chatOpen) {
            closeChat();
            return;
        }
        openChat();
    });

    chatCloseBtn.addEventListener('click', closeChat);
    chatOverlay.addEventListener('click', closeChat);

    chatSendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    initializeSession();

    setInterval(function() {
        if (chatOpen) {
            loadMessages();
        }
    }, 3000);
});
</script>
