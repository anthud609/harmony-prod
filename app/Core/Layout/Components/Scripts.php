<?php

// File: app/Core/Layout/Components/Scripts.php

namespace App\Core\Layout\Components;

class Scripts
{
    private GlobalScripts $globalScripts;

    public function __construct(GlobalScripts $globalScripts)
    {
        $this->globalScripts = $globalScripts;
    }

    public function render(array $data = []): void
    {
        $user = $data['user'] ?? [];
        ?>
        <script>
            // User data from PHP session - properly escaped for JavaScript context
            const userData = {
                firstName: <?= ejs($user['firstName'] ?? 'Guest') ?>,
                lastName: <?= ejs($user['lastName'] ?? 'User') ?>,
                fullName: <?= ejs(($user['firstName'] ?? 'Guest') . ' ' . ($user['lastName'] ?? 'User')) ?>,
                initials: <?= ejs(strtoupper(substr($user['firstName'] ?? 'G', 0, 1) . substr($user['lastName'] ?? 'U', 0, 1))) ?>,
                role: <?= ejs($user['role'] ?? 'user') ?>,
                jobTitle: <?= ejs($user['jobTitle'] ?? 'Employee') ?>,
                notificationCount: <?= (int)($user['notificationCount'] ?? 0) ?>,
                preferredTheme: <?= ejs($user['preferredTheme'] ?? 'system') ?>
            };
            
            // Update notification badge
            function updateNotificationBadge(count) {
                const badge = document.getElementById('notificationBadge');
                if (!badge && count > 0) {
                    const btn = document.getElementById('notificationBtn');
                    const newBadge = document.createElement('span');
                    newBadge.id = 'notificationBadge';
                    newBadge.className = 'absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center px-1';
                    newBadge.textContent = count > 99 ? '99+' : count;
                    btn.appendChild(newBadge);
                } else if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            }
            
            // Toggle notifications
            function toggleNotifications() {
                userData.notificationCount = 0;
                updateNotificationBadge(0);
                
                fetch('/notifications/mark-read', { 
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).catch(err => console.log('Error marking notifications as read'));
            }
            
            // Wait for DOM to be ready
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    document.documentElement.classList.remove('no-transitions');
                }, 100);
                
                const themeIcon = document.getElementById('themeIcon');
                
                function updateThemeIcon() {
                    if (document.documentElement.classList.contains('dark')) {
                        themeIcon.className = 'fas fa-sun text-gray-700 dark:text-gray-300';
                    } else {
                        themeIcon.className = 'fas fa-moon text-gray-700 dark:text-gray-300';
                    }
                }
                
                updateThemeIcon();

                window.toggleDarkMode = function() {
                    document.documentElement.classList.add('theme-transition');
                    
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                        userData.preferredTheme = 'light';
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                        userData.preferredTheme = 'dark';
                    }
                    
                    updateThemeIcon();
                    
                    fetch('/user/preferences', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'theme=' + userData.preferredTheme
                    }).catch(err => console.log('Error saving theme preference'));
                    
                    setTimeout(() => {
                        document.documentElement.classList.remove('theme-transition');
                    }, 300);
                }
            });

            // Sidebar state management
            let sidebarOpen = true;
            
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const main = document.getElementById('mainContent');
                sidebarOpen = !sidebarOpen;
                
                if (sidebarOpen) {
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-64');
                    main.classList.remove('lg:pl-16');
                    main.classList.add('lg:pl-64');
                    
                    document.querySelectorAll('.sidebar-text').forEach(el => {
                        el.classList.remove('hidden');
                    });
                    document.querySelectorAll('.sidebar-badge').forEach(el => {
                        el.classList.remove('hidden');
                    });
                    
                    if (localStorage.getItem('employeesDropdownOpen') === 'true') {
                        document.getElementById('employeesDropdown').classList.remove('hidden');
                    }
                    if (localStorage.getItem('attendanceDropdownOpen') === 'true') {
                        document.getElementById('attendanceDropdown').classList.remove('hidden');
                    }
                } else {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-16');
                    main.classList.remove('lg:pl-64');
                    main.classList.add('lg:pl-16');
                    
                    document.querySelectorAll('.sidebar-text').forEach(el => {
                        el.classList.add('hidden');
                    });
                    document.querySelectorAll('.sidebar-badge').forEach(el => {
                        el.classList.add('hidden');
                    });
                    
                    document.getElementById('employeesDropdown').classList.add('hidden');
                    document.getElementById('attendanceDropdown').classList.add('hidden');
                }
            }

            // Mobile menu
            let mobileMenuOpen = false;
            
            function toggleMobileMenu() {
                mobileMenuOpen = !mobileMenuOpen;
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('mobileMenuOverlay');
                
                if (mobileMenuOpen) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }

            // Dropdown management
            function toggleDropdown(id) {
                const dropdown = document.getElementById(id);
                const icon = document.getElementById(id + 'Icon');
                
                const allDropdowns = ['employeesDropdown', 'attendanceDropdown'];
                allDropdowns.forEach(dropdownId => {
                    if (dropdownId !== id) {
                        document.getElementById(dropdownId).classList.add('hidden');
                        document.getElementById(dropdownId + 'Icon').classList.remove('rotate-180');
                        localStorage.setItem(dropdownId + 'Open', 'false');
                    }
                });
                
                if (dropdown.classList.contains('hidden')) {
                    dropdown.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                    localStorage.setItem(id + 'Open', 'true');
                } else {
                    dropdown.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                    localStorage.setItem(id + 'Open', 'false');
                }
            }

            // User menu
            function toggleUserMenu() {
                const menu = document.getElementById('userMenu');
                menu.classList.toggle('hidden');
            }

            // Close user menu when clicking outside
            document.addEventListener('click', function(e) {
                const userMenuButton = e.target.closest('[onclick="toggleUserMenu()"]');
                const userMenu = document.getElementById('userMenu');
                
                if (!userMenuButton && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
                
                if (window.innerWidth >= 1024) {
                    const sidebar = document.getElementById('sidebar');
                    const dropdownButton = e.target.closest('[onclick*="toggleDropdown"]');
                    
                    if (!sidebar.contains(e.target) || (!dropdownButton && !e.target.closest('#employeesDropdown') && !e.target.closest('#attendanceDropdown'))) {
                        const allDropdowns = ['employeesDropdown', 'attendanceDropdown'];
                        allDropdowns.forEach(dropdownId => {
                            document.getElementById(dropdownId).classList.add('hidden');
                            document.getElementById(dropdownId + 'Icon').classList.remove('rotate-180');
                            localStorage.setItem(dropdownId + 'Open', 'false');
                        });
                    }
                }
            });

            // Initialize dropdowns based on saved state
            window.addEventListener('DOMContentLoaded', function() {
                if (localStorage.getItem('employeesDropdownOpen') === 'true') {
                    toggleDropdown('employeesDropdown');
                } else if (localStorage.getItem('attendanceDropdownOpen') === 'true') {
                    toggleDropdown('attendanceDropdown');
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024 && mobileMenuOpen) {
                    toggleMobileMenu();
                }
            });
        </script>
        
        <?php $this->globalScripts->render(); ?>
        <?php
    }
}
