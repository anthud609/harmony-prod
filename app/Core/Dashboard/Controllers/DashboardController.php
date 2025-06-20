<?php
// File: app/Core/Dashboard/Controllers/DashboardController.php (Updated Example)
// File: app/Core/Dashboard/Controllers/DashboardController.php (Updated Example)
namespace App\Core\Dashboard\Controllers;

use App\Core\Layout\LayoutManager;

class DashboardController
{
    protected LayoutManager $layout;
    
    public function __construct()
    {
        $this->layout = new LayoutManager();
    }
    
    public function index(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        // Prepare data for the view
        $data = [
            'title' => 'Dashboard – Harmony HRMS',
            'pageTitle' => 'Dashboard',
            'pageDescription' => 'Monitor your organization\'s key metrics and recent activities',
            'stats' => $this->getDashboardStats(),
            'recentActivities' => $this->getRecentActivities(),
            'helpLink' => 'https://docs.harmonyhrms.com/dashboard',
            'pageId' => 'dashboard-home',
            'isFavorite' => in_array('dashboard-home', $_SESSION['user']['favorites'] ?? [])
        ];
        
        // Set breadcrumbs
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Dashboard']
        ];
        
        // Set page actions
        $pageActions = [
            [
                'label' => 'Export Report',
                'icon' => 'fas fa-download',
                'variant' => 'secondary',
                'onclick' => 'exportDashboard(); return false;'
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
                        'onclick' => 'refreshDashboard(); return false;'
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
                        'onclick' => 'resetDashboard(); return false;',
                        'danger' => true
                    ]
                ]
            ],
            [
                'label' => 'Add Widget',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
                'onclick' => 'openAddWidgetModal(); return false;'
            ]
        ];
        
        // Render the view with the layout
        $this->layout
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/dashboard.php');
    }
    
    protected function getDashboardStats(): array
    {
        return [
            'totalEmployees' => 284,
            'presentToday' => 270,
            'onLeave' => 14,
            'newApplications' => 8
        ];
    }
    
    protected function getRecentActivities(): array
    {
        return [
            [
                'type' => 'new_employee',
                'icon' => 'fas fa-user-plus',
                'color' => 'blue',
                'message' => '<span class="font-medium">Sarah Johnson</span> joined as Senior Developer',
                'time' => '2 hours ago'
            ],
            [
                'type' => 'leave_approved',
                'icon' => 'fas fa-check',
                'color' => 'green',
                'message' => 'Leave request approved for <span class="font-medium">Michael Chen</span>',
                'time' => '5 hours ago'
            ],
            [
                'type' => 'leave_request',
                'icon' => 'fas fa-calendar',
                'color' => 'orange',
                'message' => '<span class="font-medium">Emma Davis</span> requested leave for Dec 25-27',
                'time' => 'Yesterday'
            ]
        ];
    }
}