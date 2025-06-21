<?php
// File: app/Modules/IAM/Controllers/InboxController.php

namespace App\Modules\IAM\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Message;
use App\Modules\IAM\Models\User;

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

    public function index(Request $request): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            return (new Response())->redirect('/login');
        }

        // Get user from session
        $user = $this->sessionManager->getUser();
        $userId = $user['id'] ?? null;

        if (!$userId) {
            $this->logError('User ID not found in session');
            return (new Response())->redirect('/login');
        }

        // Get conversations (grouped messages)
        $conversations = $this->getConversations($userId);
        $pinnedConversations = array_filter($conversations, fn($conv) => $conv['isPinned'] ?? false);
        $recentConversations = array_filter($conversations, fn($conv) => !($conv['isPinned'] ?? false));

        // Prepare data for the view
        $data = [
            'title' => 'Messages – Harmony HRMS',
            'pageTitle' => 'Messages',
            'pageDescription' => 'Manage your conversations and messages',
            'pinnedChats' => array_values($pinnedConversations),
            'recentChats' => array_values($recentConversations),
            'totalUnread' => $this->getTotalUnreadCount($userId),
            'helpLink' => 'https://docs.harmonyhrms.com/messages',
            'pageId' => 'messages',
            'isFavorite' => in_array('messages', $user['favorites'] ?? []),
        ];

        // Set breadcrumbs and actions
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Messages'],
        ];
        $pageActions = $this->getPageActions();

        // Capture layout output
        ob_start();
        $this->layoutManager
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/inbox.php');
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    private function getConversations(string $userId): array
    {
        try {
            // Get all unique conversations
            $conversations = [];
            
            // Get all messages where user is sender or recipient
            $messages = Message::with(['sender', 'recipient'])
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                          ->orWhere('recipient_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Group messages by conversation (between two users)
            $groupedMessages = [];
            foreach ($messages as $message) {
                // Create a unique key for the conversation
                $otherUserId = $message->sender_id === $userId ? $message->recipient_id : $message->sender_id;
                $conversationKey = $otherUserId;
                
                if (!isset($groupedMessages[$conversationKey])) {
                    $groupedMessages[$conversationKey] = [];
                }
                $groupedMessages[$conversationKey][] = $message;
            }

            // Format each conversation
            foreach ($groupedMessages as $otherUserId => $conversationMessages) {
                // Get the latest message
                $latestMessage = $conversationMessages[0];
                
                // Get the other user
                $otherUser = User::find($otherUserId);
                if (!$otherUser) {
                    continue; // Skip if user not found
                }

                // Count unread messages in this conversation
                $unreadCount = 0;
                foreach ($conversationMessages as $msg) {
                    if ($msg->recipient_id === $userId && !$msg->is_read) {
                        $unreadCount++;
                    }
                }

                $conversations[] = [
                    'id' => $otherUserId, // Use other user's ID as conversation ID
                    'user' => [
                        'name' => $otherUser->full_name ?? $otherUser->first_name . ' ' . $otherUser->last_name,
                        'avatar' => [
                            'initials' => $this->getInitials($otherUser),
                            'gradient' => $this->getGradient($otherUser->id)
                        ],
                        'status' => 'offline', // You can implement online status later
                        'department' => $otherUser->department->name ?? 'Unknown'
                    ],
                    'lastMessage' => [
                        'text' => $latestMessage->body ? substr(strip_tags($latestMessage->body), 0, 50) . '...' : 'No message',
                        'time' => $this->formatTime($latestMessage->created_at),
                        'isUnread' => !$latestMessage->is_read && $latestMessage->recipient_id === $userId
                    ],
                    'unreadCount' => $unreadCount,
                    'isPinned' => $latestMessage->is_pinned ?? false,
                    'url' => '/messages/conversation/' . $otherUserId,
                    'lastMessageTime' => $latestMessage->created_at // For sorting
                ];
            }

            // Sort conversations by last message time
            usort($conversations, function($a, $b) {
                return $b['lastMessageTime'] <=> $a['lastMessageTime'];
            });

            // Remove the temporary sorting field
            foreach ($conversations as &$conv) {
                unset($conv['lastMessageTime']);
            }

            return $conversations;
            
        } catch (\Exception $e) {
            $this->logError('Failed to fetch conversations', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return [];
        }
    }

    private function getTotalUnreadCount(string $userId): int
    {
        try {
            return Message::where('recipient_id', $userId)
                ->where('is_read', false)
                ->where('is_archived', false)
                ->count();
        } catch (\Exception $e) {
            $this->logError('Failed to get unread count', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return 0;
        }
    }

    private function getPageActions(): array
    {
        return [
            [
                'label' => 'New Message',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
                'onclick' => 'openNewMessageModal(); return false;',
            ],
            [
                'type' => 'dropdown',
                'id' => 'inbox-actions',
                'label' => 'Actions',
                'icon' => 'fas fa-ellipsis-h',
                'variant' => 'secondary',
                'items' => [
                    [
                        'label' => 'Mark All Read',
                        'icon' => 'fas fa-envelope-open',
                        'onclick' => 'markAllAsRead(); return false;',
                    ],
                    [
                        'label' => 'Archive All',
                        'icon' => 'fas fa-archive',
                        'onclick' => 'archiveAll(); return false;',
                    ],
                    'divider',
                    [
                        'label' => 'Settings',
                        'icon' => 'fas fa-cog',
                        'url' => '/settings/notifications',
                    ],
                ],
            ],
        ];
    }

    private function getInitials($user): string
    {
        if (!$user) {
            return 'U';
        }
        
        $firstName = $user->first_name ?? '';
        $lastName = $user->last_name ?? '';
        
        if (empty($firstName) && empty($lastName)) {
            return 'U';
        }
        
        return strtoupper(
            substr($firstName, 0, 1) . 
            substr($lastName, 0, 1)
        );
    }

    private function getGradient(string $userId): string
    {
        $gradients = [
            'from-blue-400 to-blue-600',
            'from-green-400 to-green-600',
            'from-purple-400 to-purple-600',
            'from-red-400 to-red-600',
            'from-yellow-400 to-yellow-600',
            'from-pink-400 to-pink-600',
            'from-indigo-400 to-indigo-600',
            'from-gray-400 to-gray-600',
        ];
        
        // Use a hash of the user ID to consistently pick a gradient
        $index = hexdec(substr(md5($userId), 0, 2)) % count($gradients);
        return $gradients[$index];
    }

    private function formatTime($timestamp): string
    {
        if (!$timestamp) {
            return '';
        }

        $now = new \DateTime();
        $time = $timestamp instanceof \DateTime ? $timestamp : new \DateTime($timestamp);
        $diff = $now->getTimestamp() - $time->getTimestamp();

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return $time->format('M j');
        }
    }
}