<?php
/**
 * Employee Chat Panel - Customer Messages
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../database/connect_database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php?error=not_logged_in');
    exit;
}

$page_title = 'Customer Messages';
$current_page = 'customer-messages';
$custom_title = 'Customer Messages - MinC Project';

// Load user data for authorization check (employee only and active role).
$user = [
    'full_name' => 'Guest User',
    'user_type_status' => null,
    'user_level_id' => null
];

try {
    $user_query = "
        SELECT 
            u.user_id,
            CONCAT(u.fname, ' ', u.lname) AS full_name,
            ul.user_type_status,
            u.user_status,
            u.user_level_id
        FROM users u
        LEFT JOIN user_levels ul ON u.user_level_id = ul.user_level_id
        WHERE u.user_id = :user_id
          AND u.user_status = 'active'
        LIMIT 1
    ";

    $stmt = $pdo->prepare($user_query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_data) {
        $user = [
            'full_name' => trim((string)($user_data['full_name'] ?? 'Guest User')),
            'user_type_status' => $user_data['user_type_status'] ?? null,
            'user_level_id' => $user_data['user_level_id'] ?? null
        ];
    }
} catch (Exception $e) {
    error_log('Error fetching user data in customer-messages.php: ' . $e->getMessage());
}

$isEmployeeRole = isset($user['user_level_id']) && (int)$user['user_level_id'] === 2;
$isRoleActive = isset($user['user_type_status']) && strtolower((string)$user['user_type_status']) === 'active';
if (!$isEmployeeRole || !$isRoleActive) {
    header('Location: ../../index.php?error=unauthorized');
    exit;
}

$resolveDisplayName = static function ($name, $email, $sessionId = '') {
    $value = trim((string)$name);
    $generic = ['customer', 'anonymous customer', 'anonymous', 'guest', 'user', 'unknown'];

    if ($value !== '' && !in_array(strtolower($value), $generic, true)) {
        return $value;
    }

    $email = trim((string)$email);
    if ($email !== '' && strpos($email, '@') !== false) {
        $localPart = explode('@', $email)[0];
        $localPart = preg_replace('/[._-]+/', ' ', $localPart);
        $localPart = trim(preg_replace('/\s+/', ' ', (string)$localPart));
        if ($localPart !== '') {
            return ucwords(strtolower($localPart), " -'");
        }
    }

    if (preg_match('/^user[_-]?(\d+)$/i', (string)$sessionId, $matches)) {
        return 'User #' . $matches[1];
    }

    return 'Customer';
};

$formatConversationTime = static function ($timestamp) {
    if (empty($timestamp)) {
        return '';
    }

    $time = strtotime((string)$timestamp);
    if (!$time) {
        return '';
    }

    $today = strtotime(date('Y-m-d'));
    $messageDay = strtotime(date('Y-m-d', $time));

    if ($messageDay === $today) {
        return date('g:i A', $time);
    }

    return date('M d', $time);
};

$current_session = isset($_GET['session_id']) && trim((string)$_GET['session_id']) !== ''
    ? trim((string)$_GET['session_id'])
    : null;

$conversations = [];
$current_messages = [];
$unread_count = 0;
$selectedConv = null;

try {
    $convQuery = "
        SELECT 
            session_id,
            MAX(CASE WHEN sender_type = 'customer' THEN sender_name END) AS sender_name,
            MAX(CASE WHEN sender_type = 'customer' THEN sender_email END) AS sender_email,
            MAX(created_at) AS last_message_time,
            COUNT(*) AS total_messages,
            SUM(CASE WHEN sender_type = 'customer' THEN 1 ELSE 0 END) AS customer_messages,
            SUM(CASE WHEN is_read = 0 AND sender_type = 'customer' THEN 1 ELSE 0 END) AS unread_count
        FROM chat_messages
        WHERE session_id IS NOT NULL
          AND session_id != ''
        GROUP BY session_id
        ORDER BY last_message_time DESC
    ";

    $convStmt = $pdo->query($convQuery);
    $conversations = $convStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($conversations as &$conv) {
        $displayName = $resolveDisplayName(
            $conv['sender_name'] ?? '',
            $conv['sender_email'] ?? '',
            $conv['session_id'] ?? ''
        );
        $displayEmailRaw = trim((string)($conv['sender_email'] ?? ''));

        $conv['_display_name'] = $displayName;
        $conv['_display_email_raw'] = $displayEmailRaw;
        $conv['_display_email'] = $displayEmailRaw !== '' ? $displayEmailRaw : 'No email provided';
        $conv['_avatar'] = strtoupper(substr($displayName, 0, 1));
        $conv['_time_label'] = $formatConversationTime($conv['last_message_time'] ?? null);

        if ($current_session !== null && ($conv['session_id'] ?? '') === $current_session) {
            $selectedConv = $conv;
        }
    }
    unset($conv);

    if ($current_session) {
        $msgQuery = "
            SELECT *
            FROM chat_messages
            WHERE session_id = :session_id
            ORDER BY created_at ASC
        ";
        $msgStmt = $pdo->prepare($msgQuery);
        $msgStmt->execute([':session_id' => $current_session]);
        $current_messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

        $updateQuery = "
            UPDATE chat_messages
            SET is_read = 1, read_at = NOW()
            WHERE session_id = :session_id
              AND sender_type = 'customer'
              AND is_read = 0
        ";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([':session_id' => $current_session]);
    }

    $unreadQuery = "
        SELECT COUNT(*) AS unread_count
        FROM chat_messages
        WHERE sender_type = 'customer'
          AND is_read = 0
    ";
    $unreadStmt = $pdo->query($unreadQuery);
    $unreadResult = $unreadStmt->fetch(PDO::FETCH_ASSOC);
    $unread_count = (int)($unreadResult['unread_count'] ?? 0);
} catch (Exception $e) {
    error_log('Chat admin error in customer-messages.php: ' . $e->getMessage());
}

$additional_styles = <<<CSS
.messages-shell {
    min-height: calc(100vh - 180px);
}

.messages-card {
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.messages-grid {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    min-height: 620px;
}

.conversation-pane {
    border-right: 1px solid #e5e7eb;
    background: #fff;
}

.conversation-scroll {
    max-height: calc(100vh - 320px);
    overflow-y: auto;
}

.conversation-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 14px 16px;
    border-bottom: 1px solid #eef2f7;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

.conversation-item:hover {
    background: #f8fafc;
}

.conversation-item.active {
    background: #eef6fb;
    border-left: 3px solid #08415c;
    padding-left: 13px;
}

.conversation-avatar {
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: #dbeafe;
    color: #1e3a8a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.conversation-name {
    font-size: 0.92rem;
    line-height: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.conversation-email {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 2px;
}

.conversation-count {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 4px;
}

.conversation-time {
    font-size: 0.72rem;
    color: #64748b;
}

.chat-pane {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
}

.chat-header {
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
}

.chat-stream {
    overflow-y: auto;
    padding: 18px;
    max-height: calc(100vh - 420px);
}

.chat-stream::-webkit-scrollbar,
.conversation-scroll::-webkit-scrollbar {
    width: 8px;
}

.chat-stream::-webkit-scrollbar-thumb,
.conversation-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}

.chat-row {
    display: flex;
    gap: 8px;
    margin-bottom: 14px;
    justify-content: flex-start;
}

.chat-row.outgoing {
    justify-content: flex-end;
}

.chat-row.outgoing .chat-stack {
    align-items: flex-end;
}

.chat-row.outgoing .chat-meta {
    justify-content: flex-end;
}

.chat-stack {
    display: flex;
    flex-direction: column;
    max-width: min(78%, 420px);
    align-items: flex-start;
}

.chat-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.chat-name {
    font-size: 0.76rem;
    font-weight: 700;
    color: #0f172a;
}

.chat-email {
    font-size: 0.72rem;
    color: #64748b;
}

.chat-time {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 4px;
    display: inline-block;
}

.chat-bubble {
    border-radius: 14px;
    padding: 10px 12px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    word-break: break-word;
    white-space: pre-wrap;
    text-align: left;
}

.chat-bubble.customer {
    background: #fff;
    color: #0f172a;
    border: 1px solid #dbe3ee;
}

.chat-bubble.agent {
    background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
    color: #fff;
}

.chat-start-marker {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.chat-start-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 9999px;
    border: 1px solid #dbe3ee;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    line-height: 1;
    padding: 0.35rem 0.8rem;
}

.chat-input-wrap {
    border-top: 1px solid #e5e7eb;
    background: #fff;
    padding: 12px 16px;
}

.chat-input-area {
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 12px;
    resize: none;
    min-height: 44px;
    max-height: 140px;
    line-height: 1.25rem;
}

.chat-input-area:focus {
    outline: none;
    border-color: #08415c;
    box-shadow: 0 0 0 3px rgba(8, 65, 92, 0.15);
}

.chat-send-btn {
    border-radius: 10px;
    padding: 0 16px;
    min-height: 44px;
    font-weight: 600;
}

@media (max-width: 1024px) {
    .messages-grid {
        grid-template-columns: 1fr;
    }

    .conversation-pane {
        border-right: 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .conversation-scroll {
        max-height: 320px;
    }

    .chat-stream {
        max-height: 58vh;
    }

    .chat-stack {
        max-width: 86%;
    }
}
CSS;

ob_start();
?>
<section class="messages-shell">
    <div class="professional-card rounded-2xl overflow-hidden messages-card">
        <div class="px-6 py-5 border-b border-gray-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-[#08415c]">Customer Messages</h1>
                    <p class="text-sm text-gray-500 mt-1">Respond to support inquiries with full conversation context.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-slate-100 rounded-xl px-4 py-2 text-center">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Conversations</p>
                        <p class="text-xl font-bold text-slate-800"><?php echo count($conversations); ?></p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2 text-center">
                        <p class="text-xs uppercase tracking-wide text-red-500">Unread</p>
                        <p class="text-xl font-bold text-red-600"><?php echo $unread_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="messages-grid">
            <aside class="conversation-pane">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Inbox</p>
                </div>

                <div class="conversation-scroll">
                    <?php if (empty($conversations)): ?>
                        <div class="h-64 flex items-center justify-center px-4 text-center text-gray-500">
                            <div>
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                                <p class="font-semibold">No conversations yet</p>
                                <p class="text-sm mt-1">Incoming customer chats will appear here.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <?php
                            $sessionId = (string)($conv['session_id'] ?? '');
                            $isSelected = $current_session !== null && $current_session === $sessionId;
                            $unread = (int)($conv['unread_count'] ?? 0);
                            ?>
                            <a href="customer-messages.php?session_id=<?php echo urlencode($sessionId); ?>"
                               class="conversation-item no-underline <?php echo $isSelected ? 'active' : ''; ?>"
                               data-session-id="<?php echo htmlspecialchars($sessionId); ?>"
                               onclick="return navigateToConversation(this);">
                                <span class="conversation-avatar"><?php echo htmlspecialchars($conv['_avatar'] ?: 'U'); ?></span>
                                <div class="flex-1 min-w-0">
                                    <p class="conversation-name truncate"><?php echo htmlspecialchars($conv['_display_name']); ?></p>
                                    <p class="conversation-email truncate"><?php echo htmlspecialchars($conv['_display_email']); ?></p>
                                    <p class="conversation-count"><?php echo (int)$conv['customer_messages']; ?> message<?php echo ((int)$conv['customer_messages'] === 1) ? '' : 's'; ?></p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="conversation-time"><?php echo htmlspecialchars($conv['_time_label']); ?></span>
                                    <?php if ($unread > 0): ?>
                                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-red-500 text-white text-xs font-bold">
                                            <?php echo $unread; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <section class="chat-pane flex flex-col min-h-[620px]">
                <?php if (!$current_session): ?>
                    <div class="flex-1 flex items-center justify-center px-6">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-comments text-5xl text-gray-300 mb-4 block"></i>
                            <p class="text-xl font-semibold text-gray-700">Select a conversation</p>
                            <p class="mt-1">Pick a customer on the left to view and reply.</p>
                        </div>
                    </div>
                <?php elseif (!$selectedConv): ?>
                    <div class="flex-1 flex items-center justify-center px-6">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-exclamation-circle text-5xl text-red-300 mb-4 block"></i>
                            <p class="text-xl font-semibold text-gray-700">Conversation not found</p>
                            <p class="mt-1">The selected thread may have been removed.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <header class="chat-header px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="conversation-avatar"><?php echo htmlspecialchars($selectedConv['_avatar'] ?: 'U'); ?></span>
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-slate-900 truncate"><?php echo htmlspecialchars($selectedConv['_display_name']); ?></h2>
                                <p class="text-sm text-slate-500 truncate"><?php echo htmlspecialchars($selectedConv['_display_email']); ?></p>
                            </div>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-500 text-right">
                            <span class="font-semibold text-slate-700"><?php echo (int)$selectedConv['total_messages']; ?></span> total messages
                        </div>
                    </header>

                    <div id="messages" class="chat-stream">
                        <?php if (empty($current_messages)): ?>
                            <div class="h-full flex items-center justify-center text-center text-gray-500">
                                <div>
                                    <i class="fas fa-paper-plane text-4xl text-gray-300 mb-3 block"></i>
                                    <p class="font-semibold">No messages yet</p>
                                    <p class="text-sm mt-1">Send your first response below.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="chat-start-marker">
                                <span class="chat-start-pill">Chat started</span>
                            </div>
                            <?php foreach ($current_messages as $msg): ?>
                                <?php
                                $isAdminMessage = (($msg['sender_type'] ?? '') === 'admin');
                                $msgNameRaw = trim((string)($msg['sender_name'] ?? ''));
                                $msgEmailRaw = trim((string)($msg['sender_email'] ?? ''));

                                if ($isAdminMessage) {
                                    $msgDisplayName = 'You';
                                } else {
                                    $msgDisplayName = $resolveDisplayName($msgNameRaw, $msgEmailRaw, $current_session ?? '');
                                }

                                $messageBody = htmlspecialchars_decode((string)($msg['message_content'] ?? ''), ENT_QUOTES);
                                ?>
                                <article class="chat-row <?php echo $isAdminMessage ? 'outgoing' : 'incoming'; ?>">
                                    <div class="chat-stack">
                                        <div class="chat-meta">
                                            <span class="chat-name"><?php echo htmlspecialchars($msgDisplayName); ?></span>
                                        </div>
                                        <div class="chat-bubble <?php echo $isAdminMessage ? 'agent' : 'customer'; ?>">
                                            <?php echo nl2br(htmlspecialchars($messageBody)); ?>
                                        </div>
                                        <span class="chat-time"><?php echo date('g:i A', strtotime((string)$msg['created_at'])); ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chat-input-wrap">
                        <form id="messageForm" class="flex items-end gap-2">
                            <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($current_session); ?>">
                            <textarea id="messageText"
                                      name="message"
                                      rows="1"
                                      maxlength="2000"
                                      placeholder="Type your reply..."
                                      class="chat-input-area flex-1"></textarea>
                            <button type="submit" class="chat-send-btn bg-[#08415c] hover:bg-[#0a5273] text-white transition-colors">
                                <i class="fas fa-paper-plane mr-1.5"></i>Send
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<script>
function navigateToConversation(link) {
    const sessionId = (link.getAttribute('data-session-id') || '').trim();
    if (!sessionId) {
        return false;
    }
    window.location.href = 'customer-messages.php?session_id=' + encodeURIComponent(sessionId);
    return false;
}

const messageForm = document.getElementById('messageForm');
const messageText = document.getElementById('messageText');
const messagesContainer = document.getElementById('messages');

function scrollMessagesToBottom() {
    if (!messagesContainer) return;
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function autoResizeTextarea(textarea) {
    if (!textarea) return;
    textarea.style.height = '44px';
    textarea.style.height = Math.min(textarea.scrollHeight, 140) + 'px';
}

function appendAgentMessage(message) {
    if (!messagesContainer) return;

    const row = document.createElement('article');
    row.className = 'chat-row outgoing';

    const now = new Date();
    const timeText = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });

    row.innerHTML = `
        <div class="chat-stack">
            <div class="chat-meta">
                <span class="chat-name">You</span>
            </div>
            <div class="chat-bubble agent"></div>
            <span class="chat-time">${timeText}</span>
        </div>
    `;

    const bubble = row.querySelector('.chat-bubble.agent');
    bubble.textContent = message;

    messagesContainer.appendChild(row);
    scrollMessagesToBottom();
}

if (messageText) {
    autoResizeTextarea(messageText);
    messageText.addEventListener('input', function () {
        autoResizeTextarea(this);
    });

    messageText.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            if (messageForm) {
                messageForm.requestSubmit();
            }
        }
    });
}

if (messageForm) {
    messageForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const sessionField = this.querySelector('input[name="session_id"]');
        const sessionId = sessionField ? sessionField.value : '';
        const message = messageText ? messageText.value.trim() : '';
        if (!sessionId || !message) {
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        fetch('../../backend/chat/send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message_content: message,
                sender_name: 'MinC Support',
                sender_type: 'admin',
                session_id: sessionId
            })
        })
        .then((response) => response.json())
        .then((data) => {
            if (!data || data.status !== 'success') {
                const msg = (data && data.message) ? data.message : 'Failed to send message.';
                throw new Error(msg);
            }

            appendAgentMessage(message);
            if (messageText) {
                messageText.value = '';
                autoResizeTextarea(messageText);
                messageText.focus();
            }
        })
        .catch((error) => {
            const errMessage = error && error.message ? error.message : 'Failed to send message.';
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(errMessage, 'error');
            } else if (typeof window.showAlertModal === 'function') {
                window.showAlertModal(errMessage, 'error', 'Message Error');
            } else {
                alert(errMessage);
            }
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
            }
        });
    });
}

scrollMessagesToBottom();
</script>
<?php
$content = ob_get_clean();
include 'app.php';
?>
