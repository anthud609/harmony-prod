<?php
// File: app/Core/Layout/Components/GlobalScripts.php

namespace App\Core\Layout\Components;

class GlobalScripts
{
    public function render(): void
    {
        ?>
        <script>
        // Namespace for global components
        window.HarmonyComponents = {
            CommandPalette: {
                open: false,
                selectedIndex: 0,
                searchAbortController: null,
                searchCache: new Map(),
                
                init() {
                    this.setupKeyboardShortcuts();
                    this.setupSearchHandlers();
                },
                
                open() {
                    const palette = document.getElementById('commandPalette');
                    const input = document.getElementById('commandInput');
                    
                    palette.classList.remove('hidden');
                    this.open = true;
                    
                    setTimeout(() => {
                        palette.classList.add('show');
                        input.focus();
                    }, 10);
                    
                    input.value = '';
                    this.selectedIndex = 0;
                    this.updateSelectedItem();
                },
                
                close() {
                    const palette = document.getElementById('commandPalette');
                    palette.classList.remove('show');
                    
                    // Cancel any pending search requests
                    if (this.searchAbortController) {
                        this.searchAbortController.abort();
                        this.searchAbortController = null;
                    }
                    
                    setTimeout(() => {
                        palette.classList.add('hidden');
                        this.open = false;
                    }, 200);
                },
                
                setupKeyboardShortcuts() {
                    document.addEventListener('keydown', (e) => {
                        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                            e.preventDefault();
                            this.open();
                        }
                        
                        if (e.key === 'Escape' && this.open) {
                            this.close();
                        }
                        
                        if (this.open) {
                            this.handleNavigation(e);
                        }
                    });
                },
                
                setupSearchHandlers() {
                    const input = document.getElementById('commandInput');
                    if (input) {
                        // Debounce search input
                        let debounceTimer;
                        input.addEventListener('input', (e) => {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                this.handleSearch(e.target.value);
                            }, 300); // 300ms debounce
                        });
                    }
                },
                
                handleNavigation(e) {
                    const items = document.querySelectorAll('.command-item');
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                        this.updateSelectedItem();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                        this.updateSelectedItem();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const activeItem = document.querySelector('.command-item.active');
                        if (activeItem) {
                            activeItem.click();
                            this.close();
                        }
                    }
                },
                
                updateSelectedItem() {
                    const items = document.querySelectorAll('.command-item');
                    items.forEach((item, index) => {
                        if (index === this.selectedIndex) {
                            item.classList.add('active');
                            item.scrollIntoView({ block: 'nearest' });
                        } else {
                            item.classList.remove('active');
                        }
                    });
                },
                
                async handleSearch(query) {
                    if (query.length === 0) {
                        // Show default content
                        document.getElementById('searchResults').classList.add('hidden');
                        document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
                            el.classList.remove('hidden');
                        });
                        return;
                    }
                    
                    // Check cache first
                    const cacheKey = query.toLowerCase();
                    if (this.searchCache.has(cacheKey)) {
                        this.displaySearchResults(this.searchCache.get(cacheKey));
                        return;
                    }
                    
                    // Cancel previous request if exists
                    if (this.searchAbortController) {
                        this.searchAbortController.abort();
                    }
                    
                    // Create new abort controller for this request
                    this.searchAbortController = new AbortController();
                    
                    // Show loading state
                    const searchResults = document.getElementById('searchResults');
                    searchResults.innerHTML = `
                        <div class="p-4 text-center">
                            <div class="inline-flex items-center text-gray-500 dark:text-gray-400">
                                <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Searching...</span>
                            </div>
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                    
                    // Hide default sections
                    document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
                        el.classList.add('hidden');
                    });
                    
                    try {
                        const response = await fetch('/api/search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
                            },
                            body: JSON.stringify({ 
                                query: query,
                                limit: 20
                            }),
                            signal: this.searchAbortController.signal
                        });
                        
                        if (!response.ok) {
                            throw new Error(`Search failed: ${response.status}`);
                        }
                        
                        const results = await response.json();
                        
                        // Cache the results
                        this.searchCache.set(cacheKey, results);
                        
                        // Clear old cache entries if too many
                        if (this.searchCache.size > 50) {
                            const firstKey = this.searchCache.keys().next().value;
                            this.searchCache.delete(firstKey);
                        }
                        
                        this.displaySearchResults(results);
                        
                    } catch (error) {
                        if (error.name === 'AbortError') {
                            // Request was cancelled, ignore
                            return;
                        }
                        
                        console.error('Search error:', error);
                        
                        searchResults.innerHTML = `
                            <div class="p-4">
                                <div class="flex items-center text-red-600 dark:text-red-400 mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">Search failed</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    ${error.message || 'Unable to search. Please try again.'}
                                </p>
                                <button onclick="CommandPalette.retrySearch('${query.replace(/'/g, "\\'")}')" 
                                        class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                    Try again
                                </button>
                            </div>
                        `;
                    } finally {
                        this.searchAbortController = null;
                    }
                },
                
                displaySearchResults(results) {
                    const searchResults = document.getElementById('searchResults');
                    
                    if (!results || results.length === 0) {
                        searchResults.innerHTML = `
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>No results found</p>
                                <p class="text-sm mt-1">Try a different search term</p>
                            </div>
                        `;
                        return;
                    }
                    
                    // Group results by type
                    const grouped = results.reduce((acc, item) => {
                        if (!acc[item.type]) acc[item.type] = [];
                        acc[item.type].push(item);
                        return acc;
                    }, {});
                    
                    let html = '<div class="p-2">';
                    
                    // Render each group
                    Object.entries(grouped).forEach(([type, items]) => {
                        const typeLabel = {
                            'employee': 'Employees',
                            'document': 'Documents',
                            'action': 'Actions'
                        }[type] || type;
                        
                        html += `
                            <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                ${typeLabel}
                            </p>
                        `;
                        
                        items.forEach(item => {
                            if (item.type === 'action') {
                                html += `
                                    <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item"
                                            data-action="${item.action}">
                                        <div class="w-10 h-10 bg-${item.color}-100 dark:bg-${item.color}-900 rounded-lg flex items-center justify-center mr-3">
                                            <i class="${item.icon} text-${item.color}-600 dark:text-${item.color}-400"></i>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">${item.title}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">${item.description}</p>
                                        </div>
                                    </button>
                                `;
                            } else {
                                html += `
                                    <a href="${item.url}" 
                                       class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item">
                                        <div class="w-10 h-10 bg-${item.color}-100 dark:bg-${item.color}-900 rounded-lg flex items-center justify-center mr-3">
                                            <i class="${item.icon} text-${item.color}-600 dark:text-${item.color}-400"></i>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">${item.title}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">${item.description}</p>
                                        </div>
                                    </a>
                                `;
                            }
                        });
                    });
                    
                    html += '</div>';
                    searchResults.innerHTML = html;
                    
                    // Reset selected index
                    this.selectedIndex = 0;
                    this.updateSelectedItem();
                },
                
                retrySearch(query) {
                    // Clear cache for this query to force fresh search
                    this.searchCache.delete(query.toLowerCase());
                    this.handleSearch(query);
                }
            },
            
            Messages: {
                toggle() {
                    const dropdown = document.getElementById('messagesDropdown');
                    const notifDropdown = document.getElementById('notificationsDropdown');
                    
                    notifDropdown.classList.add('hidden');
                    dropdown.classList.toggle('hidden');
                    
                    if (!dropdown.classList.contains('hidden')) {
                        this.markAsRead();
                        this.loadMessages();
                    }
                },
                
                async loadMessages() {
                    try {
                        const response = await fetch('/api/messages', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) throw new Error('Failed to load messages');
                        
                        const data = await response.json();
                        this.renderMessages(data.messages);
                        
                    } catch (error) {
                        console.error('Error loading messages:', error);
                        this.showError();
                    }
                },
                
                renderMessages(messages) {
                    const container = document.querySelector('#messagesDropdown .max-h-96');
                    if (!messages || messages.length === 0) {
                        container.innerHTML = `
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                <p>No messages</p>
                            </div>
                        `;
                        return;
                    }
                    
                    container.innerHTML = messages.map(message => `
                        <a href="${message.url || '#'}" 
                           class="flex items-start p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700">
                            <div class="w-10 h-10 bg-gradient-to-br ${message.avatar.gradient} rounded-full flex items-center justify-center text-white font-medium mr-3 flex-shrink-0">
                                ${message.avatar.initials}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">${message.sender}</h4>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">${message.time}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">${message.preview}</p>
                            </div>
                        </a>
                    `).join('');
                },
                
                showError() {
                    const container = document.querySelector('#messagesDropdown .max-h-96');
                    container.innerHTML = `
                        <div class="p-4 text-center">
                            <p class="text-red-600 dark:text-red-400">Failed to load messages</p>
                            <button onclick="Messages.loadMessages()" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">
                                Try again
                            </button>
                        </div>
                    `;
                },
                
                async markAsRead() {
                    try {
                        const response = await fetch('/api/messages/mark-read', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
                            }
                        });
                        
                        if (response.ok) {
                            const badge = document.getElementById('messageBadge');
                            if (badge) badge.remove();
                        }
                    } catch (error) {
                        console.error('Error marking messages as read:', error);
                    }
                }
            },
            
            Notifications: {
                toggle() {
                    const dropdown = document.getElementById('notificationsDropdown');
                    const messagesDropdown = document.getElementById('messagesDropdown');
                    
                    messagesDropdown.classList.add('hidden');
                    dropdown.classList.toggle('hidden');
                    
                    if (!dropdown.classList.contains('hidden')) {
                        this.loadNotifications();
                        this.markAsViewed();
                    }
                },
                
                async loadNotifications() {
                    try {
                        const response = await fetch('/api/notifications', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) throw new Error('Failed to load notifications');
                        
                        const data = await response.json();
                        this.renderNotifications(data.notifications);
                        
                    } catch (error) {
                        console.error('Error loading notifications:', error);
                        this.showError();
                    }
                },
                
                renderNotifications(notifications) {
                    const container = document.querySelector('#notificationsDropdown .max-h-96');
                    if (!notifications || notifications.length === 0) {
                        container.innerHTML = `
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                <p>No notifications</p>
                            </div>
                        `;
                        return;
                    }
                    
                    container.innerHTML = notifications.map(notification => `
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-100 dark:border-gray-700 ${!notification.read ? 'bg-blue-50 dark:bg-blue-900/20' : ''}">
                            <div class="flex items-start">
                                <div class="w-10 h-10 bg-${notification.color}-100 dark:bg-${notification.color}-900 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="${notification.icon} text-${notification.color}-600 dark:text-${notification.color}-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white mb-1">
                                        ${notification.message}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${notification.time}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                },
                
                showError() {
                    const container = document.querySelector('#notificationsDropdown .max-h-96');
                    container.innerHTML = `
                        <div class="p-4 text-center">
                            <p class="text-red-600 dark:text-red-400">Failed to load notifications</p>
                            <button onclick="Notifications.loadNotifications()" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">
                                Try again
                            </button>
                        </div>
                    `;
                },
                
                async markAsViewed() {
                    try {
                        await fetch('/api/notifications/mark-viewed', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
                            }
                        });
                    } catch (error) {
                        console.error('Error marking notifications as viewed:', error);
                    }
                },
                
                async markAllRead() {
                    try {
                        const response = await fetch('/api/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
                            }
                        });
                        
                        if (response.ok) {
                            this.updateBadge(0);
                            
                            // Visual feedback
                            document.querySelectorAll('#notificationsDropdown .border-b').forEach(item => {
                                item.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                            });
                            
                            // Reload notifications to get updated state
                            this.loadNotifications();
                        }
                    } catch (error) {
                        console.error('Error marking notifications as read:', error);
                    }
                },
                
                updateBadge(count) {
                    const badge = document.getElementById('notificationBadge');
                    if (count === 0 && badge) {
                        badge.remove();
                    } else if (count > 0) {
                        if (!badge) {
                            const btn = document.getElementById('notificationBtn');
                            const newBadge = document.createElement('span');
                            newBadge.id = 'notificationBadge';
                            newBadge.className = 'absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1';
                            btn.appendChild(newBadge);
                        }
                        badge.textContent = count > 99 ? '99+' : count;
                    }
                }
            }
        };
        
        // Global click handler for closing dropdowns
        document.addEventListener('click', function(e) {
            // Messages dropdown
            if (!e.target.closest('#messagesBtn') && !e.target.closest('#messagesDropdown')) {
                document.getElementById('messagesDropdown')?.classList.add('hidden');
            }
            
            // Notifications dropdown
            if (!e.target.closest('#notificationBtn') && !e.target.closest('#notificationsDropdown')) {
                document.getElementById('notificationsDropdown')?.classList.add('hidden');
            }
        });
        
        // Initialize components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            HarmonyComponents.CommandPalette.init();
        });
        
        // Expose shortcuts for inline onclick handlers
        window.CommandPalette = HarmonyComponents.CommandPalette;
        window.Messages = HarmonyComponents.Messages;
        window.Notifications = HarmonyComponents.Notifications;
        </script>

        <script>
// CSRF Token Management
window.CsrfToken = {
    token: '<?= csrf_token() ?>',
    
    getToken() {
        return this.token;
    },
    
    refreshToken() {
        // Optionally implement token refresh via AJAX
        return this.token;
    },
    
    // Add CSRF token to all AJAX requests
    setupAjaxDefaults() {
        // For native fetch
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            options.headers = options.headers || {};
            
            // Add CSRF token to non-GET requests
            if (!options.method || options.method.toUpperCase() !== 'GET') {
                options.headers['X-CSRF-Token'] = CsrfToken.getToken();
            }
            
            return originalFetch(url, options);
        };
        
        // For XMLHttpRequest
        const open = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(method, url) {
            open.apply(this, arguments);
            
            if (method.toUpperCase() !== 'GET') {
                this.setRequestHeader('X-CSRF-Token', CsrfToken.getToken());
            }
        };
    }
};

// Initialize CSRF for AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    CsrfToken.setupAjaxDefaults();
});
</script>

        <?php
    }
}
