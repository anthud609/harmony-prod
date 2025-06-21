<?php
// File: app/Core/Layout/Components/Messages.php (FIXED)

namespace App\Core\Layout\Components;

use App\Modules\IAM\Models\Message;
use App\Core\Traits\LoggerTrait;

class Messages
{
    use LoggerTrait;

    public function render(array $data = []): void
    {
        $this->renderDropdown($data);
    }

    public function renderDropdown(array $data = []): void
    {
        $user = $data['user'] ?? [];
        $userId = $user['id'] ?? null;
        
        // Get messages from database
        $messages = $this->getMessages($userId);
        $unreadCount = $this->getUnreadCount($userId);
        
        ?>
        <!-- Messages -->
        <div class="relative">
            <button id="messagesBtn" onclick="Messages.toggle()" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-comment-dots text-gray-700 dark:text-gray-300"></i>
                <?php if ($unreadCount > 0) : ?>
                <span id="messageBadge" class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-indigo-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1">
                    <?= $unreadCount ?>
                </span>
                <?php endif; ?>
            </button>
            
            <!-- Messages Dropdown -->
            <div id="messagesDropdown" class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Messages</h3>
                        <a href="/messages" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View all</a>
                    </div>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <?php if (empty($messages)) : ?>
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <p>No messages</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($messages as $message) : ?>
                            <?php $this->renderMessageItem($message); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function renderMessageItem(array $message): void
    {
        ?>
        <a href="<?= $message['url'] ?? '#' ?>" 
           class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 <?= !$message['is_read'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' ?>">
            <div class="w-10 h-10 bg-gradient-to-br <?= e($message['avatar']['gradient']) ?> rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                <?= e($message['avatar']['initials']) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= e($message['sender']) ?></h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= e($message['time']) ?></span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2"><?= e($message['preview']) ?></p>
                <?php if (!$message['is_read']) : ?>
                    <div class="mt-1">
                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                    </div>
                <?php endif; ?>
            </div>
        </a>
        <?php
    }

    /**
     * Get messages from database
     */
    private function getMessages(?string $userId, int $limit = 5): array
    {
        if (!$userId) {
            return [];
        }

        try {
            // Get recent messages from database
            $messages = Message::with('sender')
                ->where('recipient_id', $userId)
                ->where('is_archived', false)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            // Format for frontend
            return $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender->full_name ?? $message->sender->first_name . ' ' . $message->sender->last_name,
                    'preview' => $message->preview ?? substr(strip_tags($message->body), 0, 100) . '...',
                    'time' => $this->formatTime($message->created_at),
                    'is_read' => $message->is_read,
                    'url' => '/messages/' . $message->id,
                    'avatar' => [
                        'initials' => $this->getInitials($message->sender),
                        'gradient' => $this->getAvatarGradient($message->sender->id)
                    ]
                ];
            })->toArray();

        } catch (\Exception $e) {
            $this->logError('Failed to load messages for header', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            // Return fallback data
            return [];
        }
    }

    /**
     * Get unread message count
     */
    private function getUnreadCount(?string $userId): int
    {
        if (!$userId) {
            return 0;
        }

        try {
            return Message::where('recipient_id', $userId)
                ->where('is_read', false)
                ->where('is_archived', false)
                ->count();
                
        } catch (\Exception $e) {
            $this->logError('Failed to get unread message count', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get user initials
     */
    private function getInitials($user): string
    {
        if (!$user) {
            return 'U';
        }
        
        $firstName = $user->first_name ?? '';
        $lastName = $user->last_name ?? '';
        
        return strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
    }

    /**
     * Get avatar gradient based on user ID
     */
    private function getAvatarGradient(string $userId): string
    {
        $gradients = [
            'from-green-400 to-blue-500',
            'from-purple-400 to-pink-500',
            'from-orange-400 to-red-500',
            'from-indigo-400 to-purple-500',
            'from-blue-400 to-cyan-500',
            'from-pink-400 to-red-500',
            'from-yellow-400 to-orange-500',
            'from-teal-400 to-green-500',
        ];
        
        // Use user ID to consistently pick a gradient
        $index = hexdec(substr(md5($userId), 0, 2)) % count($gradients);
        return $gradients[$index];
    }

    /**
     * Format time difference
     */
    private function formatTime($timestamp): string
    {
        if (!$timestamp) {
            return 'just now';
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