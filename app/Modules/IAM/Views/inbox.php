<?php
// File: app/Modules/IAM/Views/inbox.php

// Get user data and messages
$user = $data['user'] ?? [];
$pinnedChats = $data['pinnedChats'] ?? [];
$recentChats = $data['recentChats'] ?? [];
$groupChats = $data['groupChats'] ?? [];
$currentChat = $data['currentChat'] ?? null;
$messages = $data['messages'] ?? [];
?>

<div class="flex h-[calc(100vh-8rem)] bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar - Chat List -->
    <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <!-- Search and Compose -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="relative mb-3">
                <input type="text" 
                       id="chatSearch"
                       placeholder="Search messages, people, groups..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 dark:text-gray-500"></i>
            </div>
            <button onclick="composeNewMessage()" 
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors text-sm font-medium">
                <i class="fas fa-edit"></i>
                New Chat
            </button>
        </div>

        <!-- Chat Filters -->
        <div class="flex items-center gap-1 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
            <button onclick="filterChats('all')" 
                    class="chat-filter active px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                All
            </button>
            <button onclick="filterChats('unread')" 
                    class="chat-filter px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                Unread
            </button>
            <button onclick="filterChats('groups')" 
                    class="chat-filter px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                Groups
            </button>
            <button onclick="filterChats('mentions')" 
                    class="chat-filter px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                @Mentions
            </button>
        </div>

        <!-- Chat List -->
        <div class="flex-1 overflow-y-auto">
            <!-- Pinned Chats -->
            <?php if (!empty($pinnedChats)) : ?>
            <div class="px-2 py-2">
                <div class="flex items-center justify-between px-2 mb-1">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Pinned</span>
                    <button onclick="togglePinnedSection()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                </div>
                <div id="pinnedChats">
                    <?php foreach ($pinnedChats as $chat) : ?>
                        <?php renderChatItem($chat, true); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Chats -->
            <div class="px-2 py-2">
                <div class="flex items-center justify-between px-2 mb-1">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Recent</span>
                </div>
                <div id="recentChats">
                    <?php foreach ($recentChats as $chat) : ?>
                        <?php renderChatItem($chat); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Group Chats -->
            <?php if (!empty($groupChats)) : ?>
            <div class="px-2 py-2">
                <div class="flex items-center justify-between px-2 mb-1">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Groups</span>
                </div>
                <div id="groupChats">
                    <?php foreach ($groupChats as $chat) : ?>
                        <?php renderChatItem($chat, false, true); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col">
        <?php if ($currentChat) : ?>
            <!-- Chat Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <?php if ($currentChat['type'] === 'group') : ?>
                            <div class="w-12 h-12 bg-gradient-to-br <?= e($currentChat['gradient']) ?> rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-white text-lg"></i>
                            </div>
                        <?php else : ?>
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-br <?= e($currentChat['gradient']) ?> rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                    <?= e($currentChat['initials']) ?>
                                </div>
                                <div class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <?= e($currentChat['name']) ?>
                                <?php if ($currentChat['verified'] ?? false) : ?>
                                    <i class="fas fa-check-circle text-blue-500 text-sm"></i>
                                <?php endif; ?>
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php if ($currentChat['type'] === 'group') : ?>
                                    <?= e($currentChat['memberCount']) ?> members
                                <?php else : ?>
                                    <?= e($currentChat['status']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button onclick="startVideoCall()" 
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                title="Start video call">
                            <i class="fas fa-video text-gray-600 dark:text-gray-300"></i>
                        </button>
                        <button onclick="startVoiceCall()" 
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                title="Start voice call">
                            <i class="fas fa-phone text-gray-600 dark:text-gray-300"></i>
                        </button>
                        <button onclick="toggleChatInfo()" 
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                title="Chat info">
                            <i class="fas fa-info-circle text-gray-600 dark:text-gray-300"></i>
                        </button>
                        <button onclick="toggleChatOptions()" 
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                title="More options">
                            <i class="fas fa-ellipsis-v text-gray-600 dark:text-gray-300"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 px-6 py-4" id="messagesContainer">
                <!-- Date Separator -->
                <div class="flex items-center justify-center my-4">
                    <div class="bg-gray-200 dark:bg-gray-700 px-3 py-1 rounded-full">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Today</span>
                    </div>
                </div>

                <?php foreach ($messages as $message) : ?>
                    <?php renderMessage($message, $user); ?>
                <?php endforeach; ?>
            </div>

            <!-- Message Input -->
            <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-end gap-3">
                    <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-plus text-gray-600 dark:text-gray-300"></i>
                    </button>
                    
                    <div class="flex-1 relative">
                        <textarea 
                            id="messageInput"
                            placeholder="Type a message..."
                            rows="1"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                            onkeydown="handleMessageKeydown(event)"
                            oninput="adjustTextareaHeight(this)"></textarea>
                        
                        <div class="absolute right-2 bottom-2 flex items-center gap-1">
                            <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                <i class="fas fa-smile text-gray-400 dark:text-gray-500 text-sm"></i>
                            </button>
                            <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                <i class="fas fa-paperclip text-gray-400 dark:text-gray-500 text-sm"></i>
                            </button>
                            <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
                                <i class="fas fa-at text-gray-400 dark:text-gray-500 text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button onclick="sendMessage()" 
                            class="p-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                
                <!-- Typing Indicator -->
                <div id="typingIndicator" class="hidden mt-2 text-xs text-gray-500 dark:text-gray-400">
                    <i class="fas fa-ellipsis-h animate-pulse"></i> <span id="typingUsers">Someone is typing...</span>
                </div>
            </div>
        <?php else : ?>
            <!-- No Chat Selected -->
            <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                <div class="text-center">
                    <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-gray-400 dark:text-gray-500 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Select a conversation</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Choose a chat from the list to start messaging</p>
                    <button onclick="composeNewMessage()" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors text-sm font-medium">
                        <i class="fas fa-edit"></i>
                        Start New Chat
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Sidebar - Chat Info (Hidden by default) -->
    <div id="chatInfoPanel" class="hidden w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 overflow-y-auto">
        <!-- Chat info content will be loaded dynamically -->
    </div>
</div>

<!-- Styles -->
<style>
    .chat-filter {
        color: rgb(107 114 128);
    }
    
    .dark .chat-filter {
        color: rgb(156 163 175);
    }
    
    .chat-filter:hover {
        background-color: rgb(243 244 246);
        color: rgb(55 65 81);
    }
    
    .dark .chat-filter:hover {
        background-color: rgb(55 65 81);
        color: rgb(229 231 235);
    }
    
    .chat-filter.active {
        background-color: rgb(239 246 255);
        color: rgb(79 70 229);
    }
    
    .dark .chat-filter.active {
        background-color: rgb(55 48 163);
        color: rgb(224 231 255);
    }
    
    .chat-item {
        transition: all 0.15s ease;
    }
    
    .chat-item:hover {
        background-color: rgb(249 250 251);
    }
    
    .dark .chat-item:hover {
        background-color: rgb(55 65 81);
    }
    
    .chat-item.active {
        background-color: rgb(239 246 255);
        border-left-color: rgb(79 70 229);
    }
    
    .dark .chat-item.active {
        background-color: rgb(55 48 163);
        border-left-color: rgb(99 102 241);
    }
    
    .message-bubble {
        max-width: 70%;
        word-wrap: break-word;
    }
    
    .message-bubble.own {
        background-color: rgb(79 70 229);
        color: white;
    }
    
    .message-bubble.other {
        background-color: rgb(243 244 246);
        color: rgb(17 24 39);
    }
    
    .dark .message-bubble.other {
        background-color: rgb(55 65 81);
        color: rgb(243 244 246);
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
    
    .message-item {
        animation: slideIn 0.3s ease-out;
    }
</style>

<!-- JavaScript -->
<script>
// Global state
let currentChatId = <?= json_encode($currentChat['id'] ?? null) ?>;
let currentFilter = 'all';
let typingTimer = null;
let isTyping = false;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set up real-time updates
    if (currentChatId) {
        setupRealtimeUpdates();
    }
    
    // Set up search
    document.getElementById('chatSearch').addEventListener('input', debounce(searchChats, 300));
    
    // Auto-resize message input
    adjustTextareaHeight(document.getElementById('messageInput'));
});

// Chat list functions
function filterChats(filter) {
    currentFilter = filter;
    
    // Update active filter button
    document.querySelectorAll('.chat-filter').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter chats
    // This would typically make an API call
    console.log('Filtering chats by:', filter);
}

function searchChats(event) {
    const query = event.target.value.toLowerCase();
    
    // Search through chat items
    document.querySelectorAll('.chat-item').forEach(item => {
        const name = item.querySelector('.chat-name').textContent.toLowerCase();
        const lastMessage = item.querySelector('.chat-last-message').textContent.toLowerCase();
        
        if (name.includes(query) || lastMessage.includes(query)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Message functions
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Send message via API
    fetch('/api/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
        },
        body: JSON.stringify({
            chatId: currentChatId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            input.value = '';
            adjustTextareaHeight(input);
            
            // Add message to UI
            addMessageToUI(data.message);
            
            // Scroll to bottom
            scrollToBottom();
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        showNotification('Failed to send message', 'error');
    });
}

function handleMessageKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
    
    // Typing indicator
    handleTypingIndicator();
}

function handleTypingIndicator() {
    if (!isTyping) {
        isTyping = true;
        // Send typing status
        sendTypingStatus(true);
    }
    
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        isTyping = false;
        sendTypingStatus(false);
    }, 1000);
}

function sendTypingStatus(typing) {
    // This would send typing status via WebSocket or API
    console.log('Typing:', typing);
}

function adjustTextareaHeight(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// UI functions
function toggleChatInfo() {
    const panel = document.getElementById('chatInfoPanel');
    
    if (panel.classList.contains('hidden')) {
        // Load chat info
        loadChatInfo();
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

function loadChatInfo() {
    // This would load chat info via API
    const panel = document.getElementById('chatInfoPanel');
    panel.innerHTML = `
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Chat Info</h3>
            <!-- Chat details would be loaded here -->
        </div>
    `;
}

function pinChat(chatId) {
    // Pin/unpin chat via API
    console.log('Toggling pin for chat:', chatId);
}

function muteChat(chatId) {
    // Mute/unmute chat via API
    console.log('Toggling mute for chat:', chatId);
}

function deleteChat(chatId) {
    if (confirm('Are you sure you want to delete this chat?')) {
        // Delete chat via API
        console.log('Deleting chat:', chatId);
    }
}

function composeNewMessage() {
    // Open new message composer
    console.log('Opening new message composer');
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function addMessageToUI(message) {
    const container = document.getElementById('messagesContainer');
    const messageHtml = createMessageHTML(message);
    container.insertAdjacentHTML('beforeend', messageHtml);
}

function createMessageHTML(message) {
    // This would create the message HTML based on the message data
    return `<div class="message-item"><!-- Message content --></div>`;
}

function showNotification(message, type = 'info') {
    // Show notification to user
    console.log(`${type}: ${message}`);
}

// Real-time updates
function setupRealtimeUpdates() {
    // This would set up WebSocket or polling for real-time updates
    console.log('Setting up real-time updates for chat:', currentChatId);
}
</script>

<?php
// Helper function to render chat items
function renderChatItem($chat, $isPinned = false, $isGroup = false) {
    $unreadClass = $chat['unread'] > 0 ? 'font-semibold' : '';
    $activeClass = $chat['active'] ?? false ? 'active' : '';
    ?>
    <div class="chat-item <?= $activeClass ?> relative flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer border-l-4 border-transparent"
         onclick="openChat('<?= e($chat['id']) ?>')">
        
        <!-- Avatar -->
        <div class="relative flex-shrink-0">
            <?php if ($isGroup || $chat['type'] === 'group') : ?>
                <div class="w-10 h-10 bg-gradient-to-br <?= e($chat['gradient']) ?> rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-white text-sm"></i>
                </div>
            <?php else : ?>
                <div class="w-10 h-10 bg-gradient-to-br <?= e($chat['gradient']) ?> rounded-full flex items-center justify-center text-white font-medium">
                    <?= e($chat['initials']) ?>
                </div>
                <?php if ($chat['online'] ?? false) : ?>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Chat Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-0.5">
                <span class="chat-name text-sm <?= $unreadClass ?> text-gray-900 dark:text-white truncate">
                    <?= e($chat['name']) ?>
                </span>
                <span class="text-xs <?= $unreadClass ?> text-gray-500 dark:text-gray-400">
                    <?= e($chat['time']) ?>
                </span>
            </div>
            <div class="flex items-center justify-between">
                <p class="chat-last-message text-xs <?= $unreadClass ?> text-gray-600 dark:text-gray-400 truncate pr-2">
                    <?php if ($chat['typing'] ?? false) : ?>
                        <i class="fas fa-ellipsis-h animate-pulse"></i> typing...
                    <?php else : ?>
                        <?php if ($chat['lastMessageFrom'] ?? false) : ?>
                            <span class="font-medium"><?= e($chat['lastMessageFrom']) ?>:</span>
                        <?php endif; ?>
                        <?= e($chat['lastMessage']) ?>
                    <?php endif; ?>
                </p>
                <div class="flex items-center gap-1">
                    <?php if ($isPinned) : ?>
                        <i class="fas fa-thumbtack text-xs text-gray-400 dark:text-gray-500"></i>
                    <?php endif; ?>
                    <?php if ($chat['muted'] ?? false) : ?>
                        <i class="fas fa-bell-slash text-xs text-gray-400 dark:text-gray-500"></i>
                    <?php endif; ?>
                    <?php if ($chat['unread'] > 0) : ?>
                        <span class="bg-indigo-600 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center">
                            <?= $chat['unread'] > 99 ? '99+' : $chat['unread'] ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Hover Actions -->
        <div class="absolute right-2 top-2 hidden group-hover:flex items-center gap-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-1">
            <button onclick="event.stopPropagation(); pinChat('<?= e($chat['id']) ?>')" 
                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    title="<?= $isPinned ? 'Unpin' : 'Pin' ?> chat">
                <i class="fas fa-thumbtack text-xs text-gray-600 dark:text-gray-400"></i>
            </button>
            <button onclick="event.stopPropagation(); muteChat('<?= e($chat['id']) ?>')" 
                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    title="<?= ($chat['muted'] ?? false) ? 'Unmute' : 'Mute' ?> notifications">
                <i class="fas fa-bell<?= ($chat['muted'] ?? false) ? '-slash' : '' ?> text-xs text-gray-600 dark:text-gray-400"></i>
            </button>
            <button onclick="event.stopPropagation(); deleteChat('<?= e($chat['id']) ?>')" 
                    class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                    title="Delete chat">
                <i class="fas fa-trash text-xs text-gray-600 dark:text-gray-400"></i>
            </button>
        </div>
    </div>
    <?php
}

// Helper function to render messages
function renderMessage($message, $currentUser) {
    $isOwn = $message['senderId'] === $currentUser['id'];
    $alignClass = $isOwn ? 'justify-end' : 'justify-start';
    $bubbleClass = $isOwn ? 'own' : 'other';
    ?>
    <div class="message-item flex <?= $alignClass ?> mb-4 group">
        <?php if (!$isOwn) : ?>
            <!-- Other user's avatar -->
            <div class="flex-shrink-0 mr-3">
                <div class="w-8 h-8 bg-gradient-to-br <?= e($message['senderGradient']) ?> rounded-full flex items-center justify-center text-white text-xs font-medium">
                    <?= e($message['senderInitials']) ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="flex flex-col <?= $isOwn ? 'items-end' : 'items-start' ?> max-w-[70%]">
            <?php if (!$isOwn && ($message['showName'] ?? true)) : ?>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1 ml-2">
                    <?= e($message['senderName']) ?>
                </span>
            <?php endif; ?>
            
            <div class="message-bubble <?= $bubbleClass ?> px-4 py-2 rounded-2xl <?= $isOwn ? 'rounded-br-sm' : 'rounded-bl-sm' ?>">
                <?php if ($message['replyTo'] ?? false) : ?>
                    <div class="border-l-2 border-white/30 pl-2 mb-1 opacity-70">
                        <p class="text-xs"><?= e($message['replyTo']['text']) ?></p>
                    </div>
                <?php endif; ?>
                
                <p class="text-sm whitespace-pre-wrap"><?= e($message['text']) ?></p>
                
                <?php if ($message['attachments'] ?? false) : ?>
                    <div class="mt-2">
                        <!-- Render attachments -->
                    </div>
                <?php endif; ?>
                
                <?php if ($message['reactions'] ?? false) : ?>
                    <div class="flex items-center gap-1 mt-1">
                        <?php foreach ($message['reactions'] as $reaction) : ?>
                            <span class="inline-flex items-center gap-1 bg-white/20 rounded-full px-2 py-0.5 text-xs">
                                <?= e($reaction['emoji']) ?> <?= e($reaction['count']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-2 mt-1 px-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    <?= e($message['time']) ?>
                </span>
                <?php if ($isOwn) : ?>
                    <?php if ($message['read'] ?? false) : ?>
                        <i class="fas fa-check-double text-xs text-blue-500"></i>
                    <?php elseif ($message['delivered'] ?? false) : ?>
                        <i class="fas fa-check-double text-xs text-gray-400"></i>
                    <?php else : ?>
                        <i class="fas fa-check text-xs text-gray-400"></i>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message actions (hidden by default) -->
            <div class="hidden group-hover:flex items-center gap-1 mt-1">
                <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors">
                    <i class="fas fa-reply text-xs text-gray-500 dark:text-gray-400"></i>
                </button>
                <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors">
                    <i class="fas fa-smile text-xs text-gray-500 dark:text-gray-400"></i>
                </button>
                <button class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors">
                    <i class="fas fa-ellipsis-h text-xs text-gray-500 dark:text-gray-400"></i>
                </button>
            </div>
        </div>
        
        <?php if ($isOwn) : ?>
            <!-- Current user's avatar -->
            <div class="flex-shrink-0 ml-3">
                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-medium">
                    <?= e($currentUser['initials'] ?? 'ME') ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>