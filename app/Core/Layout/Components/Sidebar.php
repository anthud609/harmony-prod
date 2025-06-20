<?php

// File: app/Core/Layout/Components/Sidebar.php

namespace App\Core\Layout\Components;

class Sidebar
{
    public function render(array $data = []): void
    {
        ?>
        <aside id="sidebar"
               class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 transition-all duration-300 z-40 overflow-hidden">
            <nav class="flex flex-col h-full">
                <div class="px-3 py-4">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Navigation</p>
                </div>
                <div class="flex-1 overflow-y-auto px-3 space-y-1">
                    <?php
                    $menuItems = $this->getMenuItems();

                    foreach ($menuItems as $item) :
                        if (isset($item['dropdown'])) : ?>
                            <div>
                                <button onclick="toggleDropdown('<?= strtolower($item['label']) ?>Dropdown')"
                                        class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                                    <i class="<?= $item['icon'] ?> w-5 text-center"></i>
                                    <span class="sidebar-text text-sm font-medium"><?= $item['label'] ?></span>
                                    <i id="<?= strtolower($item['label']) ?>DropdownIcon" class="fas fa-chevron-down ml-auto text-xs transition-transform duration-200 sidebar-text"></i>
                                </button>
                                <div id="<?= strtolower($item['label']) ?>Dropdown" class="hidden pl-10 space-y-1 mt-1">
                                                <?php foreach ($item['dropdown'] as $subItem) : ?>
                                    <a href="<?= $subItem['href'] ?>"
                                       class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
                                        <i class="<?= $subItem['icon'] ?> w-5 text-center text-xs"></i>
                                        <span class="sidebar-text"><?= $subItem['label'] ?></span>
                                    </a>
                                                <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <a href="<?= $item['href'] ?>"
                               class="flex items-center space-x-3 px-3 py-2 rounded-lg <?= isset($item['active']) && $item['active'] ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?> transition-all duration-200">
                                <i class="<?= $item['icon'] ?> w-5 text-center"></i>
                                <span class="sidebar-text text-sm font-medium"><?= $item['label'] ?></span>
                                        <?php if (isset($item['badge'])) : ?>
                                <span class="sidebar-badge ml-auto bg-<?= $item['badge']['color'] ?>-100 dark:bg-<?= $item['badge']['color'] ?>-700 text-<?= $item['badge']['color'] ?>-600 dark:text-<?= $item['badge']['color'] ?>-300 px-2 py-0.5 rounded text-xs font-medium">
                                            <?= $item['badge']['text'] ?>
                                </span>
                                        <?php endif; ?>
                                        <?php if (isset($item['indicator'])) : ?>
                                <span class="ml-auto w-2 h-2 bg-<?= $item['indicator'] ?>-500 rounded-full"></span>
                                        <?php endif; ?>
                            </a>
                        <?php endif;
                    endforeach; ?>
                </div>

                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <button class="w-full flex items-center justify-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-headset mr-2"></i>
                        <span class="sidebar-text">Get Support</span>
                    </button>
                </div>
            </nav>
        </aside>
        <?php
    }

    private function getMenuItems(): array
    {
        return [
            [
                'href' => '/dashboard',
                'icon' => 'fas fa-home',
                'label' => 'Dashboard',
                'active' => true,
                'badge' => ['text' => 'New', 'color' => 'indigo'],
            ],
            [
                'icon' => 'fas fa-users',
                'label' => 'Employees',
                'dropdown' => [
                    ['href' => '/employees', 'icon' => 'fas fa-list', 'label' => 'All Employees'],
                    ['href' => '/employees/add', 'icon' => 'fas fa-user-plus', 'label' => 'Add Employee'],
                    ['href' => '/departments', 'icon' => 'fas fa-sitemap', 'label' => 'Departments'],
                ],
            ],
            [
                'icon' => 'fas fa-clock',
                'label' => 'Attendance',
                'dropdown' => [
                    ['href' => '/attendance/today', 'icon' => 'fas fa-calendar-check', 'label' => 'Today'],
                    ['href' => '/attendance/history', 'icon' => 'fas fa-history', 'label' => 'History'],
                ],
            ],
            [
                'href' => '/leave',
                'icon' => 'fas fa-calendar-alt',
                'label' => 'Leave',
                'indicator' => 'orange',
            ],
            [
                'href' => '/payroll',
                'icon' => 'fas fa-money-check-alt',
                'label' => 'Payroll',
            ],
            [
                'href' => '/reports',
                'icon' => 'fas fa-chart-bar',
                'label' => 'Reports',
            ],
            [
                'href' => '/settings',
                'icon' => 'fas fa-cog',
                'label' => 'Settings',
            ],
        ];
    }
}
