<!-- File: app/Modules/IAM/Views/inbox.php -->
<div class="p-6 max-w-7xl mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Messages</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= e($stats['total']) ?></p>
                </div>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-gray-600 dark:text-gray-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Unread</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= e($stats['unread']) ?></p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope-open-text text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Starred</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= e($stats['starred']) ?></p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 dark:text-yellow-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Archived</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= e($stats['archived']) ?></p>
                </div>
                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-archive text-gray-600 dark:text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Messages</h2>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <button class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-sort mr-1"></i> Sort
                    </button>
                </div>
            </div>
        </div>
        
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (empty($messages)) : ?>
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-gray-300 dark:text-gray-600 text-5xl mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">No messages in your inbox</p>
                </div>
            <?php else : ?>
                <?php foreach ($messages as $message) : ?>
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer <?= !$message['is_read'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' ?>"
                         onclick="openMessage('<?= e($message['id']) ?>')">
                        <div class="flex items-start space-x-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br <?= e($message['sender']['avatar']['gradient']) ?> rounded-full flex items-center justify-center text-white font-medium">
                                    <?= e($message['sender']['avatar']['initials']) ?>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            <?= e($message['sender']['name']) ?>
                                            <?php if (!$message['is_read']) : ?>
                                                <span class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                                            <?= e($message['subject']) ?>
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                            <?= e($message['preview']) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 ml-4">
                                        <span class="text-xs text-gray-500 dark:text-gray-400"><?= e($message['time']) ?></span>
                                        <?php if ($message['has_attachments']) : ?>
                                            <i class="fas fa-paperclip text-gray-400"></i>
                                        <?php endif; ?>
                                        <button onclick="toggleStar(event, '<?= e($message['id']) ?>')" class="text-gray-400 hover:text-yellow-500">
                                            <i class="<?= $message['is_starred'] ? 'fas' : 'far' ?> fa-star <?= $message['is_starred'] ? 'text-yellow-500' : '' ?>"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openMessage(messageId) {
    // Implement message viewing
    window.location.href = `/inbox/${messageId}`;
}

function toggleStar(event, messageId) {
    event.stopPropagation();
    // Implement star toggle
    console.log('Toggle star for message:', messageId);
}

function markAllAsRead() {
    // Implement mark all as read
    console.log('Mark all as read');
}

function archiveRead() {
    // Implement archive read messages
    console.log('Archive read messages');
}

function emptyTrash() {
    if (confirm('Are you sure you want to empty the trash? This action cannot be undone.')) {
        // Implement empty trash
        console.log('Empty trash');
    }
}

function openComposeModal() {
    // Implement compose modal
    console.log('Open compose modal');
}
</script>