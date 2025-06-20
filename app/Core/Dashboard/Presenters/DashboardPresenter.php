<?php

// File: app/Core/Dashboard/Presenters/DashboardPresenter.php
namespace App\Core\Dashboard\Presenters;

use App\Core\Layout\LayoutManager;

/**
 * Presentation layer - handles view rendering
 */
class DashboardPresenter
{
    private LayoutManager $layoutManager;
    
    public function __construct(LayoutManager $layoutManager)
    {
        $this->layoutManager = $layoutManager;
    }
    
    public function renderDashboard(array $data, array $user): string
    {
        // Prepare view data
        $viewData = [
            'title' => 'Dashboard – Harmony HRMS',
            'pageTitle' => 'Dashboard',
            'pageDescription' => 'Monitor your organization\'s key metrics and recent activities',
            'stats' => $data['stats'],
            'recentActivities' => $data['activities'],
            'helpLink' => 'https://docs.harmonyhrms.com/dashboard',
            'pageId' => 'dashboard-home',
            'isFavorite' => in_array('dashboard-home', $data['favorites'])
        ];
        
        // Set breadcrumbs
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Dashboard']
        ];
        
        // Set page actions
        $pageActions = $this->getPageActions();
        
        // Use output buffering to capture rendered content
        ob_start();
        
        $this->layoutManager
            ->setLayout('main')
            ->with($viewData)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/dashboard.php');
            
        return ob_get_clean();
    }
    
    private function getPageActions(): array
    {
        return [
            [
                'label' => 'Export Report',
                'icon' => 'fas fa-download',
                'variant' => 'secondary',
                'data-action' => 'export-dashboard'
            ],
            [
                'type' => 'dropdown',
                'id' => 'dashboard-actions',
                'label' => 'Actions',
                'icon' => 'fas fa-ellipsis-h',
                'variant' => 'secondary',
                'items' => [
                    [
                        'label' => 'Refresh Data',
                        'icon' => 'fas fa-sync-alt',
                        'data-action' => 'refresh-dashboard'
                    ],
                    [
                        'label' => 'Configure Widgets',
                        'icon' => 'fas fa-cog',
                        'url' => '/dashboard/configure'
                    ],
                    'divider',
                    [
                        'label' => 'Reset to Default',
                        'icon' => 'fas fa-undo',
                        'data-action' => 'reset-dashboard',
                        'danger' => true
                    ]
                ]
            ],
            [
                'label' => 'Add Widget',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
                'data-action' => 'add-widget'
            ]
        ];
    }
}