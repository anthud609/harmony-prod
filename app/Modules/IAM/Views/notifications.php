<!-- File: app/Modules/IAM/Views/notifications.php -->
<div class="p-6 max-w-7xl mx-auto">
    <!-- Filter Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex space-x-1 p-1">
            <a href="/notifications?filter=all" 
               class="flex-1 text-center px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $currentFilter === 'all' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                All
                <?php if ($stats['total'] > 0) : ?>
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= $currentFilter === 'all' ? 'bg-indigo-200 dark:bg-indigo-800' : 'bg-gray-200 dark:bg-gray-700' ?>">
                        <?= e($stats['total']) ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <a href="/notifications?filter=unread" 
               class="flex-1 text-center px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $currentFilter === 'unread' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                Unread
                <?php if ($stats['unread'] > 0) : ?>
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= $currentFilter === 'unread' ? 'bg-indigo-200 dark:bg-indigo-800' : 'bg-gray-200 dark:bg-gray-700' ?>">
                        <?= e($stats['unread']) ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <a href="/notifications?filter=starred" 
               class="flex-1 text-center px-4 py-2 text-sm font-medium rounded-lg transition-colors <?= $currentFilter === 'starred' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                Starred
                <?php if ($stats['starred'] > 0) : ?>
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= $currentFilter === 'starred' ? 'bg-indigo-200 dark:bg-indigo-800' : 'bg-gray-200 dark:bg-gray-700' ?>">
                        <?= e($stats['starred']) ?>
                    </span>
                <?php endif; ?>
            </a>
        </nav>
    </div>

    <!-- Today's Summary -->
    <?php if ($stats['today'] > 0 && $currentFilter === 'all') : ?>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-3"></i>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    You have <strong><?= e($stats['today']) ?></strong> new notification<?= $stats['today'] > 1 ? 's' : '' ?> today
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Notifications List -->
    <div class="space-y-2">
        <?php if (empty($notifications)) : ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <i class="fas fa-bell-slash text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">
                    <?php if ($currentFilter === 'unread') : ?>
                        No unread notifications
                    <?php elseif ($currentFilter === 'starred') : ?>
                        No starred notifications
                    <?php else : ?>
                        No notifications yet
                    <?php endif; ?>
                </p>
            </div>
        <?php else : ?>
            <?php
            $lastDate = '';
            foreach ($notifications as $notification) :
                $notificationDate = date('Y-m-d', strtotime($notification['timestamp']));
                $displayDate = '';
                
                if ($notificationDate !== $lastDate) {
                    $lastDate = $notificationDate;
                    if ($notificationDate === date('Y-m-d')) {
                        $displayDate = 'Today';
                    } elseif ($notificationDate === date('Y-m-d', strtotime('-1 day'))) {
                        $displayDate = 'Yesterday';
                    } else {
                        $displayDate = date('F j, Y', strtotime($notificationDate));
                    }
                }
            ?>
                <?php if ($displayDate) : ?>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-6 mb-2">
                        <?= e($displayDate) ?>
                    </h3>
                <?php endif; ?>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow <?= !$notification['is_read'] ? 'border-l-4 border-l-blue-500' : '' ?>"
                     onclick="handleNotificationClick('<?= e($notification['id']) ?>', <?= $notification['url'] ? "'" . e($notification['url']) . "'" : 'null' ?>)">
                    <div class="flex items-start">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-<?= ecss($notification['color']) ?>-100 dark:bg-<?= ecss($notification['color']) ?>-900 rounded-full flex items-center justify-center">
                                <i class="<?= attr($notification['icon']) ?> text-<?= ecss($notification['color']) ?>-600 dark:text-<?= ecss($notification['color']) ?>-400"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="ml-4 flex-1">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?= e($notification['title']) ?>
                                        <?php if (!$notification['is_read']) : ?>
                                            <span class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        <?= e($notification['message']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                        <?= e($notification['time']) ?>
                                    </p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    <button onclick="toggleNotificationStar(event, '<?= e($notification['id']) ?>')" 
                                            class="text-gray-400 hover:text-yellow-500 transition-colors">
                                        <i class="<?= $notification['is_starred'] ? 'fas' : 'far' ?> fa-star <?= $notification['is_starred'] ? 'text-yellow-500' : '' ?>"></i>
                                    </button>
                                    <div class="relative">
                                        <button onclick="toggleNotificationMenu(event, '<?= e($notification['id']) ?>')" 
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div id="menu-<?= e($notification['id']) ?>" 
                                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden z-10">
                                            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <i class="fas fa-check mr-2"></i> Mark as read
                                            </button>
                                            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <i class="fas fa-archive mr-2"></i> Archive
                                            </button>
                                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                            <button class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <i class="fas fa-trash mr-2"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function handleNotificationClick(notificationId, url) {
    // Mark as read
    markNotificationAsRead(notificationId);
    
    // Navigate if URL exists
    if (url) {
        window.location.href = url;
    }
}

function toggleNotificationStar(event, notificationId) {
    event.stopPropagation();
    // Implement star toggle
    console.log('Toggle star for notification:', notificationId);
}

function toggleNotificationMenu(event, notificationId) {
    event.stopPropagation();
    const menu = document.getElementById(`menu-${notificationId}`);
    menu.classList.toggle('hidden');
    
    // Close menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!menu.contains(e.target)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
}

function markNotificationAsRead(notificationId) {
    // Implement mark as read
    console.log('Mark notification as read:', notificationId);
}

function markAllNotificationsAsRead() {
    if (confirm('Mark all notifications as read?')) {
        // Implement mark all as read
        console.log('Mark all as read');
    }
}

function clearAllNotifications() {
    if (confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
        // Implement clear all
        console.log('Clear all notifications');
    }
}
</script>