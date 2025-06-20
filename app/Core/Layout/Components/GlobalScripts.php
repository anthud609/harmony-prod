<?php
// File: 
namespace App\Core\Layout\Components;

class GlobalScripts
{
    public static function render(): void
    {
        ?>
        <script>
        // Namespace for global components
        window.HarmonyComponents = {
            CommandPalette: {
                open: false,
                selectedIndex: 0,
                
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
                        input.addEventListener('input', (e) => this.handleSearch(e.target.value));
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
                    if (query.length > 0) {
                        // Show loading state
                        const searchResults = document.getElementById('searchResults');
                        searchResults.innerHTML = '<div class="p-4 text-center text-gray-500">Searching...</div>';
                        searchResults.classList.remove('hidden');
                        
                        // Hide default sections
                        document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
                            el.classList.add('hidden');
                        });
                        
                        // Simulate API call
                        setTimeout(() => {
                            this.displaySearchResults(query);
                        }, 300);
                    } else {
                        // Show default content
                        document.getElementById('searchResults').classList.add('hidden');
                        document.querySelectorAll('#commandResults > div:not(#searchResults)').forEach(el => {
                            el.classList.remove('hidden');
                        });
                    }
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
                    }
                },
                
                async markAsRead() {
                    // Simulate API call
                    try {
                        const response = await fetch('/api/messages/mark-read', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            // Update badge
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
                        setTimeout(() => {
                            this.updateBadge(0);
                        }, 1000);
                    }
                },
                
                async markAllRead() {
                    try {
                        const response = await fetch('/api/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            this.updateBadge(0);
                            
                            // Visual feedback
                            document.querySelectorAll('#notificationsDropdown .border-b').forEach(item => {
                                item.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                            });
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