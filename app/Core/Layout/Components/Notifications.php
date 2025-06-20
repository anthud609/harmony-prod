<?php

// File: app/Core/Layout/Components/Notifications.php

namespace App\Core\Layout\Components;

class Notifications
{
    public function render(array $data = []): void
    {
        $this->renderDropdown($data);
    }

    public function renderDropdown(array $data = []): void
    {
        $notifications = $this->getNotifications($data);
        $unreadCount = $data['user']['notificationCount'] ?? count(array_filter($notifications, fn ($n) => ! $n['read']));
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
                    <?php foreach ($notifications as $notification) : ?>
                        <?php $this->renderNotificationItem($notification); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function renderNotificationItem(array $notification): void
    {
        ?>
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 <?= ! $notification['read'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' ?>">
            <div class="flex items-start">
                <div class="w-10 h-10 bg-<?= $notification['color'] ?>-100 dark:bg-<?= $notification['color'] ?>-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <i class="<?= $notification['icon'] ?> text-<?= $notification['color'] ?>-600 dark:text-<?= $notification['color'] ?>-400"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                        <?= $notification['message'] ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $notification['time'] ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    private function getNotifications(array $data): array
    {
        // In a real app, this would come from database
        return [
            [
                'id' => 1,
                'type' => 'leave_approved',
                'icon' => 'fas fa-check',
                'color' => 'green',
                'message' => 'Your leave request for <span class="font-medium">Dec 25-27</span> has been approved',
                'time' => '10 minutes ago',
                'read' => false,
            ],
            [
                'id' => 2,
                'type' => 'new_team_member',
                'icon' => 'fas fa-user-plus',
                'color' => 'blue',
                'message' => '<span class="font-medium">Sarah Johnson</span> joined your team',
                'time' => '2 hours ago',
                'read' => false,
            ],
            [
                'id' => 3,
                'type' => 'birthday',
                'icon' => 'fas fa-birthday-cake',
                'color' => 'purple',
                'message' => 'Today is <span class="font-medium">Michael Chen\'s</span> birthday! ðŸŽ‰',
                'time' => '8:00 AM',
                'read' => true,
            ],
        ];
    }
}
