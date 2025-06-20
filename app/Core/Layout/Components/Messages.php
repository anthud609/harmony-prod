<?php

// File: app/Core/Layout/Components/Messages.php

namespace App\Core\Layout\Components;

class Messages
{
    public function render(array $data = []): void
    {
        $this->renderDropdown($data);
    }

    public function renderDropdown(array $data = []): void
    {
        $messages = $this->getMessages($data);
        $unreadCount = $data['user']['messageCount'] ?? count(array_filter($messages, fn ($m) => ! $m['read']));
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
                    <?php foreach ($messages as $message) : ?>
                        <?php $this->renderMessageItem($message); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function renderMessageItem(array $message): void
    {
        ?>
        <a href="<?= $message['url'] ?? '#' ?>" 
           class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
            <div class="w-10 h-10 bg-gradient-to-br <?= $message['avatar']['gradient'] ?> rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                <?= $message['avatar']['initials'] ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= e($message['sender']) ?></h4>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= $message['time'] ?></span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2"><?= e($message['preview']) ?></p>
            </div>
        </a>
        <?php
    }

    private function getMessages(array $data): array
    {
        // In a real app, this would come from database
        return [
            [
                'id' => 1,
                'sender' => 'Jane Doe',
                'preview' => 'Hey, can you review the Q4 report before the meeting tomorrow?',
                'time' => '5m ago',
                'read' => false,
                'avatar' => [
                    'initials' => 'JD',
                    'gradient' => 'from-green-400 to-blue-500',
                ],
            ],
            [
                'id' => 2,
                'sender' => 'Mark Robinson',
                'preview' => 'The new employee onboarding process has been updated. Please check...',
                'time' => '1h ago',
                'read' => false,
                'avatar' => [
                    'initials' => 'MR',
                    'gradient' => 'from-purple-400 to-pink-500',
                ],
            ],
            [
                'id' => 3,
                'sender' => 'Sarah Thompson',
                'preview' => 'Thanks for your help with the payroll issue!',
                'time' => '2h ago',
                'read' => true,
                'avatar' => [
                    'initials' => 'ST',
                    'gradient' => 'from-orange-400 to-red-500',
                ],
            ],
        ];
    }
}
