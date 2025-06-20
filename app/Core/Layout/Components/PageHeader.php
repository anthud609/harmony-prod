<?php
// File: app/Core/Layout/Components/PageHeader.php
namespace App\Core\Layout\Components;

class PageHeader
{
    public static function render(array $data = []): void
    {
        $title = $data['pageTitle'] ?? $data['title'] ?? 'Untitled Page';
        $description = $data['pageDescription'] ?? '';
        $breadcrumbs = $data['breadcrumbs'] ?? [];
        $actions = $data['pageActions'] ?? [];
        $helpLink = $data['helpLink'] ?? '#';
        $pageId = $data['pageId'] ?? md5($_SERVER['REQUEST_URI']);
        $isFavorite = $data['isFavorite'] ?? false;
        ?>
        
        <!-- Page Header -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
                <!-- Breadcrumbs -->
                <?php if (!empty($breadcrumbs)): ?>
                <nav class="mb-3" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index > 0): ?>
                                <li class="flex items-center">
                                    <svg class="flex-shrink-0 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </li>
                            <?php endif; ?>
                            <li>
                                <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                                    <a href="<?= htmlspecialchars($crumb['url']) ?>" 
                                       class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                                        <?= htmlspecialchars($crumb['label']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">
                                        <?= htmlspecialchars($crumb['label']) ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
                
                <!-- Title and Actions Row -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <!-- Title, Description, and Favorite -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white truncate">
                                <?= htmlspecialchars($title) ?>
                            </h1>
                            
                            <!-- Favorite Button -->
                            <button onclick="PageHeader.toggleFavorite('<?= $pageId ?>')"
                                    data-page-id="<?= $pageId ?>"
                                    class="favorite-btn group relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                                    aria-label="Toggle favorite">
                                <svg class="w-5 h-5 transition-all duration-300 <?= $isFavorite ? 'text-yellow-500 fill-current' : 'text-gray-400 dark:text-gray-500' ?>" 
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" 
                                          stroke-linejoin="round" 
                                          stroke-width="2" 
                                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                
                                <!-- Tooltip -->
                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                    <?= $isFavorite ? 'Remove from favorites' : 'Add to favorites' ?>
                                </span>
                            </button>
                            
                            <!-- Help Icon -->
                            <?php if ($helpLink !== '#'): ?>
                            <a href="<?= htmlspecialchars($helpLink) ?>"
                               target="_blank"
                               class="group relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                               aria-label="Help">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                
                                <!-- Tooltip -->
                                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                    View help
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($description): ?>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            <?= htmlspecialchars($description) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <?php if (!empty($actions)): ?>
                    <div class="mt-4 sm:mt-0 sm:ml-6 flex flex-wrap gap-2">
                        <?php foreach ($actions as $action): ?>
                            <?php if (isset($action['type']) && $action['type'] === 'dropdown'): ?>
                                <!-- Dropdown Action -->
                                <div class="relative">
                                    <button onclick="PageHeader.toggleDropdown('<?= $action['id'] ?>')"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $action['variant'] === 'primary' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' ?> transition-colors">
                                        <?php if (isset($action['icon'])): ?>
                                        <i class="<?= $action['icon'] ?> mr-2 -ml-1 w-4 h-4"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($action['label']) ?>
                                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="<?= $action['id'] ?>" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden z-10">
                                        <?php foreach ($action['items'] as $item): ?>
                                            <?php if ($item === 'divider'): ?>
                                                <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($item['url'] ?? '#') ?>"
                                                   onclick="<?= $item['onclick'] ?? '' ?>"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors <?= isset($item['danger']) && $item['danger'] ? 'text-red-600 dark:text-red-400' : '' ?>">
                                                    <?php if (isset($item['icon'])): ?>
                                                    <i class="<?= $item['icon'] ?> mr-3 w-4 h-4"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($item['label']) ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Regular Button -->
                                <a href="<?= htmlspecialchars($action['url'] ?? '#') ?>"
                                   onclick="<?= $action['onclick'] ?? '' ?>"
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= $action['variant'] === 'primary' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' ?> transition-colors">
                                    <?php if (isset($action['icon'])): ?>
                                    <i class="<?= $action['icon'] ?> mr-2 -ml-1 w-4 h-4"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($action['label']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <style>
            @keyframes starPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.2); }
            }
            
            .favorite-btn.animating svg {
                animation: starPulse 0.3s ease-in-out;
            }
            
            .favorite-btn svg {
                transition: all 0.3s ease;
            }
            
            .favorite-btn:hover svg {
                transform: scale(1.1);
            }
            
            .favorite-btn svg.filled {
                color: #eab308;
                fill: currentColor;
            }
        </style>
        
        <script>
            window.PageHeader = {
                favorites: JSON.parse(localStorage.getItem('harmonyFavorites') || '[]'),
                
                toggleFavorite(pageId) {
                    const btn = document.querySelector(`[data-page-id="${pageId}"]`);
                    const svg = btn.querySelector('svg');
                    const index = this.favorites.indexOf(pageId);
                    
                    btn.classList.add('animating');
                    
                    if (index > -1) {
                        // Remove from favorites
                        this.favorites.splice(index, 1);
                        svg.classList.remove('text-yellow-500', 'fill-current');
                        svg.classList.add('text-gray-400', 'dark:text-gray-500');
                        btn.querySelector('span').textContent = 'Add to favorites';
                    } else {
                        // Add to favorites
                        this.favorites.push(pageId);
                        svg.classList.remove('text-gray-400', 'dark:text-gray-500');
                        svg.classList.add('text-yellow-500', 'fill-current');
                        btn.querySelector('span').textContent = 'Remove from favorites';
                    }
                    
                    localStorage.setItem('harmonyFavorites', JSON.stringify(this.favorites));
                    
                    setTimeout(() => {
                        btn.classList.remove('animating');
                    }, 300);
                    
                    // Optional: Send to server
                    this.syncFavorites(pageId, index === -1);
                },
                
                async syncFavorites(pageId, isFavorite) {
                    try {
                        await fetch('/api/favorites', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ pageId, isFavorite })
                        });
                    } catch (error) {
                        console.error('Error syncing favorites:', error);
                    }
                },
                
                toggleDropdown(id) {
                    const dropdown = document.getElementById(id);
                    dropdown.classList.toggle('hidden');
                    
                    // Close on outside click
                    document.addEventListener('click', function closeDropdown(e) {
                        if (!e.target.closest(`#${id}`) && !e.target.closest(`[onclick*="${id}"]`)) {
                            dropdown.classList.add('hidden');
                            document.removeEventListener('click', closeDropdown);
                        }
                    });
                }
            };
            
            // Initialize favorites on load
            document.addEventListener('DOMContentLoaded', function() {
                const favorites = PageHeader.favorites;
                favorites.forEach(pageId => {
                    const btn = document.querySelector(`[data-page-id="${pageId}"]`);
                    if (btn) {
                        const svg = btn.querySelector('svg');
                        svg.classList.remove('text-gray-400', 'dark:text-gray-500');
                        svg.classList.add('text-yellow-500', 'fill-current');
                    }
                });
            });
        </script>
        <?php
    }
}