<?php
// File: app/Core/Layout/Components/Header.php
?>
<header class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-sm z-50">
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">
        <!-- Left: toggles + logo -->
        <div class="flex items-center">
            <button onclick="toggleMobileMenu()"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mr-2">
                <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
            </button>
            <button onclick="toggleSidebar()"
                    class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mr-4">
                <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
            </button>
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fas fa-building text-white"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 hidden sm:block">Harmony HRMS</h1>
            </div>
        </div>

        <!-- Center: search trigger -->
        <div class="hidden md:flex flex-1 max-w-md mx-8">
            <button onclick="openCommandPalette()" 
                    class="w-full flex items-center text-left px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all group">
                <i class="fas fa-search text-gray-400 dark:text-gray-500 mr-3"></i>
                <span class="text-gray-500 dark:text-gray-400 flex-1">Search anything...</span>
                <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 rounded">
                    <span class="text-xs">⌘</span>K
                </kbd>
            </button>
        </div>

        <!-- Right: actions -->
        <div class="flex items-center space-x-2">
            <!-- Search (mobile) -->
            <button onclick="openCommandPalette()" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-search text-gray-700 dark:text-gray-300"></i>
            </button>

            <!-- Messages -->
            <div class="relative">
                <button id="messagesBtn" onclick="toggleMessages()" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-comment-dots text-gray-700 dark:text-gray-300"></i>
                    <?php if (($user['messageCount'] ?? 3) > 0): ?>
                    <span id="messageBadge" class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-indigo-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1">
                        <?= ($user['messageCount'] ?? 3) ?>
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
                        <!-- Message items -->
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                                JD
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">Jane Doe</h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">5m ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">Hey, can you review the Q4 report before the meeting tomorrow?</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                                MR
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">Mark Robinson</h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">1h ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">The new employee onboarding process has been updated. Please check...</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-red-500 rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                                ST
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">Sarah Thompson</h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">2h ago</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">Thanks for your help with the payroll issue!</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" onclick="toggleNotifications()" class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-bell text-gray-700 dark:text-gray-300"></i>
                    <?php if (($user['notificationCount'] ?? 0) > 0): ?>
                    <span id="notificationBadge" class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1">
                        <?= $user['notificationCount'] > 99 ? '99+' : htmlspecialchars($user['notificationCount']) ?>
                    </span>
                    <?php endif; ?>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notificationsDropdown" class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                            <button onclick="markAllRead()" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">Mark all read</button>
                        </div>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <!-- Notification items -->
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                                        Your leave request for <span class="font-medium">Dec 25-27</span> has been approved
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">10 minutes ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-user-plus text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                                        <span class="font-medium">Sarah Johnson</span> joined your team
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-birthday-cake text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                                        Today is <span class="font-medium">Michael Chen's</span> birthday! 🎉
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">8:00 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theme toggle (desktop only) -->
            <button id="themeToggle" onclick="toggleDarkMode()"
                    class="hidden md:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i id="themeIcon" class="fas fa-moon text-gray-700 dark:text-gray-300"></i>
            </button>

            <!-- User menu -->
            <?php $this->component('userMenu', ['user' => $user]); ?>
        </div>
    </div>
</header>

<!-- Command Palette Overlay -->
<div id="commandPalette" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeCommandPalette()"></div>
    
    <!-- Command Palette Container -->
    <div class="fixed inset-x-4 top-[10vh] md:inset-x-auto md:left-1/2 md:-translate-x-1/2 md:w-full md:max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="commandPaletteContent">
            <!-- Search Input -->
            <div class="relative border-b border-gray-200 dark:border-gray-700">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                <input type="text" 
                       id="commandInput"
                       class="w-full pl-12 pr-4 py-4 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none text-lg"
                       placeholder="Search employees, documents, or type a command..."
                       autocomplete="off">
                <kbd class="absolute right-4 top-1/2 -translate-y-1/2 px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">
                    ESC
                </kbd>
            </div>
            
            <!-- Results -->
            <div id="commandResults" class="max-h-[60vh] overflow-y-auto">
                <!-- Quick Actions -->
                <div class="p-2">
                    <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quick Actions</p>
                    <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-plus text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Add New Employee</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Create a new employee profile</p>
                        </div>
                        <kbd class="px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">⌘N</kbd>
                    </button>
                    <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-plus text-green-600 dark:text-green-400"></i>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Request Leave</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Submit a new leave request</p>
                        </div>
                        <kbd class="px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded">⌘L</kbd>
                    </button>
                </div>
                
                <!-- Recent Searches -->
                <div class="p-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recent</p>
                    <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                        <i class="fas fa-clock text-gray-400 dark:text-gray-500 mr-3"></i>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Employee attendance report</span>
                    </button>
                    <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                        <i class="fas fa-clock text-gray-400 dark:text-gray-500 mr-3"></i>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sarah Johnson profile</span>
                    </button>
                </div>
                
                <!-- Dynamic Search Results (hidden by default) -->
                <div id="searchResults" class="hidden">
                    <!-- Results will be populated here -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">↑</kbd>
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">↓</kbd>
                        Navigate
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">Enter</kbd>
                        Select
                    </span>
                </div>
                <span>Type <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">/</kbd> for commands</span>
            </div>
        </div>
    </div>
</div>

<!-- Add this CSS to your layout or in a style tag -->
<style>
    .command-item:hover {
        background-color: rgba(99, 102, 241, 0.1);
    }
    .command-item.active {
        background-color: rgba(99, 102, 241, 0.15);
        outline: 2px solid rgba(99, 102, 241, 0.3);
        outline-offset: -2px;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    #commandPalette.show #commandPaletteContent {
        opacity: 1;
        transform: scale(1);
    }
</style>

<!-- Add to Scripts.php -->
<script>
// Command Palette functionality
let commandPaletteOpen = false;
let selectedIndex = 0;

function openCommandPalette() {
    const palette = document.getElementById('commandPalette');
    const input = document.getElementById('commandInput');
    const content = document.getElementById('commandPaletteContent');
    
    palette.classList.remove('hidden');
    commandPaletteOpen = true;
    
    // Trigger animation
    setTimeout(() => {
        palette.classList.add('show');
        input.focus();
    }, 10);
    
    // Reset search
    input.value = '';
    selectedIndex = 0;
    updateSelectedItem();
}

function closeCommandPalette() {
    const palette = document.getElementById('commandPalette');
    palette.classList.remove('show');
    
    setTimeout(() => {
        palette.classList.add('hidden');
        commandPaletteOpen = false;
    }, 200);
}

// Handle keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Cmd/Ctrl + K to open palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        openCommandPalette();
    }
    
    // ESC to close
    if (e.key === 'Escape' && commandPaletteOpen) {
        closeCommandPalette();
    }
    
    // Navigation in command palette
    if (commandPaletteOpen) {
        const items = document.querySelectorAll('.command-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelectedItem();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            updateSelectedItem();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const activeItem = document.querySelector('.command-item.active');
            if (activeItem) {
                activeItem.click();
                closeCommandPalette();
            }
        }
    }
});

function updateSelectedItem() {
    const items = document.querySelectorAll('.command-item');
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('active');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('active');
        }
    });
}

// Search functionality
document.getElementById('commandInput')?.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    
    if (query.length > 0) {
        // Simulate search results
        performSearch(query);
    } else {
        // Show default quick actions
        document.getElementById('searchResults').classList.add('hidden');
        document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
            el.classList.remove('hidden');
        });
    }
});

function performSearch(query) {
    // Hide default sections
    document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
        el.classList.add('hidden');
    });
    
    const searchResults = document.getElementById('searchResults');
    searchResults.classList.remove('hidden');
    
    // Simulate search results
    searchResults.innerHTML = `
        <div class="p-2">
            <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Employees</p>
            <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-medium mr-3">
                    SJ
                </div>
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Sarah Johnson</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Senior Developer • Engineering</p>
                </div>
            </button>
        </div>
        <div class="p-2 border-t border-gray-200 dark:border-gray-700">
            <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Documents</p>
            <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-file-pdf text-orange-600 dark:text-orange-400"></i>
                </div>
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Employee Handbook 2024</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Last updated 2 weeks ago</p>
                </div>
            </button>
        </div>
    `;
    
    selectedIndex = 0;
    updateSelectedItem();
}

// Messages dropdown
function toggleMessages() {
    const dropdown = document.getElementById('messagesDropdown');
    const notifDropdown = document.getElementById('notificationsDropdown');
    
    // Close notifications if open
    notifDropdown.classList.add('hidden');
    
    dropdown.classList.toggle('hidden');
}

// Notifications dropdown
function toggleNotifications() {
    const dropdown = document.getElementById('notificationsDropdown');
    const messagesDropdown = document.getElementById('messagesDropdown');
    
    // Close messages if open
    messagesDropdown.classList.add('hidden');
    
    dropdown.classList.toggle('hidden');
    
    // Mark as read when opened
    if (!dropdown.classList.contains('hidden')) {
        setTimeout(() => {
            userData.notificationCount = 0;
            updateNotificationBadge(0);
        }, 1000);
    }
}

function markAllRead() {
    userData.notificationCount = 0;
    updateNotificationBadge(0);
    
    // Visual feedback
    document.querySelectorAll('#notificationsDropdown .border-b').forEach(item => {
        item.style.opacity = '0.6';
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // Messages dropdown
    if (!e.target.closest('#messagesBtn') && !e.target.closest('#messagesDropdown')) {
        document.getElementById('messagesDropdown').classList.add('hidden');
    }
    
    // Notifications dropdown
    if (!e.target.closest('#notificationBtn') && !e.target.closest('#notificationsDropdown')) {
        document.getElementById('notificationsDropdown').classList.add('hidden');
    }
});
</script>