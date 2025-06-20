<?php

// File: app/Core/Layout/Components/Header.php

namespace App\Core\Layout\Components;

class Header
{
    private Messages $messages;
    private Notifications $notifications;
    private CommandPalette $commandPalette;
    private UserMenu $userMenu;

    public function __construct(
        Messages $messages,
        Notifications $notifications,
        CommandPalette $commandPalette,
        UserMenu $userMenu
    ) {
        $this->messages = $messages;
        $this->notifications = $notifications;
        $this->commandPalette = $commandPalette;
        $this->userMenu = $userMenu;
    }

    public function render(array $data = []): void
    {
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
                    <button onclick="CommandPalette.open()" 
                            class="w-full flex items-center text-left px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all group">
                        <i class="fas fa-search text-gray-400 dark:text-gray-500 mr-3"></i>
                        <span class="text-gray-500 dark:text-gray-400 flex-1">Search anything...</span>
                        <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 rounded">
                            <span class="text-xs">âŒ˜</span>K
                        </kbd>
                    </button>
                </div>

                <!-- Right: actions -->
                <div class="flex items-center space-x-2">
                    <!-- Search (mobile) -->
                    <button onclick="CommandPalette.open()" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-search text-gray-700 dark:text-gray-300"></i>
                    </button>

                    <?php $this->messages->renderDropdown($data); ?>
                    <?php $this->notifications->renderDropdown($data); ?>

                    <!-- Theme toggle (desktop only) -->
                    <button id="themeToggle" onclick="toggleDarkMode()"
                            class="hidden md:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i id="themeIcon" class="fas fa-moon text-gray-700 dark:text-gray-300"></i>
                    </button>

                    <!-- User menu -->
                    <?php $this->userMenu->render($data); ?>
                </div>
            </div>
        </header>

        <?php $this->commandPalette->render($data); ?>
        <?php
    }
}
