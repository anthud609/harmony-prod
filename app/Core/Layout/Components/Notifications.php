<?php
// File: app/Core/Layout/Components/Notifications.php (FIXED)

namespace App\Core\Layout\Components;

use App\Modules\IAM\Models\Notification;
use App\Core\Traits\LoggerTrait;

class Notifications
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
        
        // Get notifications from database
        $notifications = $this->getNotifications($userId);
        $unreadCount = $this->getUnreadCount($userId);
        
        ?>
        <!-- Notifications -->
        <div class="relative">
            <button id="notificationBtn" onclick="Notifications.toggle()" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-bell text-gray-700 dark:text-gray-300"></i>
                <?php if ($unreadCount > 0) : ?>
                <span id="notificationBadge" class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1">
                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                </span>
                <?php endif; ?>
            </button>
            
            <!-- Notifications Dropdown -->
            <div id="notificationsDropdown" class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                        <button onclick="Notifications.markAllRead()" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Mark all read</button>
                    </div>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <?php if (empty($notifications)) : ?>
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <p>No notifications</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($notifications as $notification) : ?>
                            <?php $this->renderNotificationItem($notification); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function renderNotificationItem(array $notification): void
    {
        ?>
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 <?= !$notification['is_read'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' ?>">
            <div class="flex items-start">
                <div class="w-10 h-10 bg-<?= ecss($notification['color']) ?>-100 dark:bg-<?= ecss($notification['color']) ?>-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="<?= attr($notification['icon']) ?> text-<?= ecss($notification['color']) ?>-600 dark:text-<?= ecss($notification['color']) ?>-400"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                        <?= $notification['message'] ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= e($notification['time']) ?></p>
                    <?php if (!$notification['is_read']) : ?>
                        <div class="mt-1">
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get notifications from database
     */
    private function getNotifications(?string $userId, int $limit = 10): array
    {
        if (!$userId) {
            return [];
        }

        try {
            // Get recent notifications from database
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            // Format for frontend
            return $notifications->map(function ($notification) {
                $displayData = $this->getDisplayData($notification->type);
                
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'icon' => $displayData['icon'],
                    'color' => $displayData['color'],
                    'message' => $notification->message,
                    'time' => $this->formatTime($notification->created_at),
                    'is_read' => $notification->is_read,
                    'url' => $notification->url ?? null,
                ];
            })->toArray();

        } catch (\Exception $e) {
            $this->logError('Failed to load notifications for header', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get unread notification count
     */
    private function getUnreadCount(?string $userId): int
    {
        if (!$userId) {
            return 0;
        }

        try {
            return Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
                
        } catch (\Exception $e) {
            $this->logError('Failed to get unread notification count', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get display data based on notification type
     */
    private function getDisplayData(string $type): array
    {
        $typeMap = [
            'leave_approved' => [
                'icon' => 'fas fa-check',
                'color' => 'green'
            ],
            'leave_rejected' => [
                'icon' => 'fas fa-times',
                'color' => 'red'
            ],
            'new_team_member' => [
                'icon' => 'fas fa-user-plus',
                'color' => 'blue'
            ],
            'birthday' => [
                'icon' => 'fas fa-birthday-cake',
                'color' => 'purple'
            ],
            'payroll_processed' => [
                'icon' => 'fas fa-money-check-alt',
                'color' => 'green'
            ],
            'document_uploaded' => [
                'icon' => 'fas fa-file-upload',
                'color' => 'blue'
            ],
            'meeting_scheduled' => [
                'icon' => 'fas fa-calendar-plus',
                'color' => 'indigo'
            ],
            'task_assigned' => [
                'icon' => 'fas fa-tasks',
                'color' => 'orange'
            ],
            'system_maintenance' => [
                'icon' => 'fas fa-tools',
                'color' => 'yellow'
            ],
            'security_alert' => [
                'icon' => 'fas fa-shield-alt',
                'color' => 'red'
            ],
            'default' => [
                'icon' => 'fas fa-info-circle',
                'color' => 'gray'
            ]
        ];

        return $typeMap[$type] ?? $typeMap['default'];
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
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $time->format('M j, Y');
        }
    }
}