<?php

// File: app/Core/Layout/Components/CommandPalette.php

namespace App\Core\Layout\Components;

class CommandPalette
{
    public function render(array $data = []): void
    {
        ?>
        <!-- Command Palette Overlay -->
        <div id="commandPalette" class="fixed inset-0 z-[100] hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="CommandPalette.close()"></div>
            
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
                        <?php $this->renderDefaultResults($data); ?>
                    </div>
                    
                    <!-- Footer -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">â†‘</kbd>
                                <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">â†“</kbd>
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

        <style>
            .command-item:hover {
                background-color: rgba(99, 102, 241, 0.1);
            }
            .command-item.active {
                background-color: rgba(99, 102, 241, 0.15);
                outline: 2px solid rgba(99, 102, 241, 0.3);
                outline-offset: -2px;
            }
            #commandPalette.show #commandPaletteContent {
                opacity: 1;
                transform: scale(1);
            }
        </style>
        <?php
    }

    private function renderDefaultResults(array $data): void
    {
        ?>
        <!-- Quick Actions -->
        <div class="p-2">
            <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quick Actions</p>
            <?php foreach ($this->getQuickActions() as $action) : ?>
            <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item"
                    data-action="<?= $action['action'] ?>">
                <div class="w-10 h-10 bg-<?= $action['color'] ?>-100 dark:bg-<?= $action['color'] ?>-900 rounded-lg flex items-center justify-center mr-3">
                    <i class="<?= $action['icon'] ?> text-<?= $action['color'] ?>-600 dark:text-<?= $action['color'] ?>-400"></i>
                </div>
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= $action['title'] ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $action['description'] ?></p>
                </div>
                <?php if (isset($action['shortcut'])) : ?>
                <kbd class="px-2 py-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded"><?= $action['shortcut'] ?></kbd>
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Recent Searches -->
        <div class="p-2 border-t border-gray-200 dark:border-gray-700">
            <p class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recent</p>
            <?php foreach ($this->getRecentSearches($data) as $search) : ?>
            <button class="w-full flex items-center px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors command-item"
                    data-search="<?= htmlspecialchars($search['query']) ?>">
                <i class="fas fa-clock text-gray-400 dark:text-gray-500 mr-3"></i>
                <span class="text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($search['query']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Dynamic Search Results (hidden by default) -->
        <div id="searchResults" class="hidden">
            <!-- Results will be populated here -->
        </div>
        <?php
    }

    private function getQuickActions(): array
    {
        return [
            [
                'action' => 'add-employee',
                'icon' => 'fas fa-user-plus',
                'color' => 'indigo',
                'title' => 'Add New Employee',
                'description' => 'Create a new employee profile',
                'shortcut' => 'âŒ˜N',
            ],
            [
                'action' => 'request-leave',
                'icon' => 'fas fa-calendar-plus',
                'color' => 'green',
                'title' => 'Request Leave',
                'description' => 'Submit a new leave request',
                'shortcut' => 'âŒ˜L',
            ],
        ];
    }

    private function getRecentSearches(array $data): array
    {
        // In a real app, this would come from user session or database
        return [
            ['query' => 'Employee attendance report'],
            ['query' => 'Sarah Johnson profile'],
        ];
    }
}
