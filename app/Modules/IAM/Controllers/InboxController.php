<?php
// File: app/Modules/IAM/Controllers/InboxController.php

namespace App\Modules\IAM\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;
use App\Modules\IAM\Models\Message;
use App\Modules\IAM\Models\Chat;
use App\Modules\IAM\Models\ChatParticipant;
use App\Core\Traits\LoggerTrait;

class InboxController
{
    use LoggerTrait;

    protected LayoutManager $layoutManager;
    protected SessionManager $sessionManager;

    public function __construct(
        LayoutManager $layoutManager,
        SessionManager $sessionManager
    ) {
        $this->layoutManager = $layoutManager;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Display inbox view
     */
    public function index(Request $request): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            return (new Response())->redirect('/login');
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];
        
        // Get current chat if specified
        $currentChatId = $request->getQuery('chat');
        $currentChat = null;
        $messages = [];
        
        if ($currentChatId) {
            $currentChat = $this->getChatDetails($currentChatId, $userId);
            if ($currentChat) {
                $messages = $this->getChatMessages($currentChatId, $userId);
                // Mark messages as read
                $this->markMessagesAsRead($currentChatId, $userId);
            }
        }

        // Get user's chats
        $pinnedChats = $this->getPinnedChats($userId);
        $recentChats = $this->getRecentChats($userId);
        $groupChats = $this->getGroupChats($userId);

        // Prepare data for view
        $data = [
            'title' => 'Messages – Harmony HRMS',
            'pageTitle' => 'Messages',
            'pageDescription' => 'Connect with your team',
            'currentChat' => $currentChat,
            'messages' => $messages,
            'pinnedChats' => $pinnedChats,
            'recentChats' => $recentChats,
            'groupChats' => $groupChats,
            'pageId' => 'inbox',
            'isFavorite' => in_array('inbox', $user['favorites'] ?? []),
        ];

        // Set breadcrumbs
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Messages'],
        ];

        // Capture layout output
        ob_start();
        $this->layoutManager
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->render(__DIR__ . '/../Views/inbox.php');
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    /**
     * Get pinned chats
     */
    private function getPinnedChats(string $userId): array
    {
        try {
            return Chat::with(['participants.user', 'lastMessage.sender'])
                ->whereHas('participants', function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('is_pinned', true);
                })
                ->orderBy('last_message_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($chat) use ($userId) {
                    return $this->formatChatItem($chat, $userId);
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->logError('Failed to get pinned chats', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get recent chats
     */
    private function getRecentChats(string $userId): array
    {
        try {
            return Chat::with(['participants.user', 'lastMessage.sender'])
                ->whereHas('participants', function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('is_pinned', false);
                })
                ->orderBy('last_message_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($chat) use ($userId) {
                    return $this->formatChatItem($chat, $userId);
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->logError('Failed to get recent chats', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get group chats
     */
    private function getGroupChats(string $userId): array
    {
        try {
            return Chat::with(['participants.user', 'lastMessage.sender'])
                ->where('type', 'group')
                ->whereHas('participants', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->orderBy('last_message_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($chat) use ($userId) {
                    return $this->formatChatItem($chat, $userId);
                })
                ->toArray();
        } catch (\Exception $e) {
            $this->logError('Failed to get group chats', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Format chat item for display
     */
    private function formatChatItem($chat, string $userId): array
    {
        $participant = $chat->participants->where('user_id', $userId)->first();
        $otherParticipants = $chat->participants->where('user_id', '!=', $userId);
        
        // Determine display name and avatar
        if ($chat->type === 'group') {
            $name = $chat->name;
            $initials = $this->getGroupInitials($name);
            $gradient = $this->getGradient($chat->id);
            $online = false;
        } else {
            // Direct message - get other user
            $otherUser = $otherParticipants->first()->user;
            $name = $otherUser->full_name;
            $initials = $this->getInitials($otherUser);
            $gradient = $this->getGradient($otherUser->id);
            $online = $this->isUserOnline($otherUser);
        }

        // Get last message info
        $lastMessage = $chat->lastMessage;
        $lastMessageText = '';
        $lastMessageFrom = null;
        $time = '';

        if ($lastMessage) {
            $lastMessageText = $this->truncateMessage($lastMessage->body);
            if ($chat->type === 'group' && $lastMessage->sender_id !== $userId) {
                $lastMessageFrom = $lastMessage->sender->first_name;
            }
            $time = $this->formatMessageTime($lastMessage->created_at);
        }

        return [
            'id' => $chat->id,
            'type' => $chat->type,
            'name' => $name,
            'initials' => $initials,
            'gradient' => $gradient,
            'online' => $online,
            'lastMessage' => $lastMessageText,
            'lastMessageFrom' => $lastMessageFrom,
            'time' => $time,
            'unread' => $participant->unread_count ?? 0,
            'muted' => $participant->is_muted ?? false,
            'pinned' => $participant->is_pinned ?? false,
            'typing' => false, // Would be updated via real-time
            'active' => false, // Set by frontend
            'memberCount' => $chat->type === 'group' ? $chat->participants->count() : null
        ];
    }

    /**
     * Get chat details
     */
    private function getChatDetails(string $chatId, string $userId): ?array
    {
        try {
            $chat = Chat::with(['participants.user'])
                ->whereHas('participants', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->find($chatId);

            if (!$chat) {
                return null;
            }

            $otherParticipants = $chat->participants->where('user_id', '!=', $userId);

            if ($chat->type === 'group') {
                return [
                    'id' => $chat->id,
                    'type' => 'group',
                    'name' => $chat->name,
                    'gradient' => $this->getGradient($chat->id),
                    'memberCount' => $chat->participants->count(),
                    'status' => 'Active group',
                    'verified' => false
                ];
            } else {
                $otherUser = $otherParticipants->first()->user;
                return [
                    'id' => $chat->id,
                    'type' => 'direct',
                    'name' => $otherUser->full_name,
                    'initials' => $this->getInitials($otherUser),
                    'gradient' => $this->getGradient($otherUser->id),
                    'status' => $this->getUserStatus($otherUser),
                    'verified' => $otherUser->verified ?? false
                ];
            }
        } catch (\Exception $e) {
            $this->logError('Failed to get chat details', [
                'chatId' => $chatId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get chat messages
     */
    private function getChatMessages(string $chatId, string $userId): array
    {
        try {
            $messages = Message::with(['sender', 'replyTo', 'attachments', 'reactions.user'])
                ->where('chat_id', $chatId)
                ->orderBy('created_at', 'asc')
                ->limit(50) // Get last 50 messages
                ->get();

            $previousSenderId = null;
            return $messages->map(function($message) use ($userId, &$previousSenderId) {
                $showName = $message->sender_id !== $previousSenderId && $message->sender_id !== $userId;
                $previousSenderId = $message->sender_id;

                return [
                    'id' => $message->id,
                    'senderId' => $message->sender_id,
                    'senderName' => $message->sender->full_name,
                    'senderInitials' => $this->getInitials($message->sender),
                    'senderGradient' => $this->getGradient($message->sender_id),
                    'text' => $message->body,
                    'time' => $message->created_at->format('g:i A'),
                    'showName' => $showName,
                    'read' => $message->read_at !== null,
                    'delivered' => true,
                    'replyTo' => $message->replyTo ? [
                        'text' => $this->truncateMessage($message->replyTo->body, 50)
                    ] : null,
                    'attachments' => $message->attachments->map(function($attachment) {
                        return [
                            'id' => $attachment->id,
                            'type' => $attachment->type,
                            'name' => $attachment->name,
                            'url' => $attachment->url
                        ];
                    })->toArray(),
                    'reactions' => $message->reactions->groupBy('emoji')->map(function($group, $emoji) {
                        return [
                            'emoji' => $emoji,
                            'count' => $group->count()
                        ];
                    })->values()->toArray()
                ];
            })->toArray();
        } catch (\Exception $e) {
            $this->logError('Failed to get chat messages', [
                'chatId' => $chatId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Mark messages as read
     */
    private function markMessagesAsRead(string $chatId, string $userId): void
    {
        try {
            Message::where('chat_id', $chatId)
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // Update unread count for participant
            ChatParticipant::where('chat_id', $chatId)
                ->where('user_id', $userId)
                ->update(['unread_count' => 0]);
        } catch (\Exception $e) {
            $this->logError('Failed to mark messages as read', [
                'chatId' => $chatId,
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper methods
     */
    private function getInitials($user): string
    {
        return strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
    }

    private function getGroupInitials(string $name): string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    private function getGradient(string $id): string
    {
        $gradients = [
            'from-blue-400 to-indigo-600',
            'from-green-400 to-teal-600',
            'from-purple-400 to-pink-600',
            'from-orange-400 to-red-600',
            'from-yellow-400 to-orange-600',
            'from-indigo-400 to-purple-600',
            'from-pink-400 to-rose-600',
            'from-teal-400 to-cyan-600',
        ];
        
        $index = hexdec(substr(md5($id), 0, 2)) % count($gradients);
        return $gradients[$index];
    }

    private function isUserOnline($user): bool
    {
        // Check if user was active in last 5 minutes
        if (!$user->last_activity_at) {
            return false;
        }
        
        return $user->last_activity_at->diffInMinutes(now()) <= 5;
    }

    private function getUserStatus($user): string
    {
        if ($this->isUserOnline($user)) {
            return 'Active now';
        }
        
        if ($user->last_activity_at) {
            return 'Last seen ' . $user->last_activity_at->diffForHumans();
        }
        
        return $user->job_title ?? 'Team member';
    }

    private function truncateMessage(string $message, int $length = 60): string
    {
        $message = strip_tags($message);
        if (strlen($message) > $length) {
            return substr($message, 0, $length) . '...';
        }
        return $message;
    }

    private function formatMessageTime($timestamp): string
    {
        $now = now();
        $time = $timestamp instanceof \DateTime ? $timestamp : new \DateTime($timestamp);
        
        if ($time->format('Y-m-d') === $now->format('Y-m-d')) {
            return $time->format('g:i A');
        } elseif ($time->format('Y-m-d') === $now->subDay()->format('Y-m-d')) {
            return 'Yesterday';
        } elseif ($time->diffInDays($now) < 7) {
            return $time->format('l'); // Day name
        } else {
            return $time->format('M j');
        }
    }
}