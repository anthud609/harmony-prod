<?php
// File: app/Core/Dashboard/Controllers/DashboardController.php
namespace App\Core\Dashboard\Controllers;

use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;

class DashboardController
{
    protected LayoutManager $layout;
    
    public function __construct()
    {
        $this->layout = new LayoutManager();
    }
    
    public function index(): void
    {
        // Check if user is logged in using SessionManager
        if (!SessionManager::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        // Get user from session
        $user = SessionManager::getUser();
        
        // Prepare data for the view
        $data = [
            'title' => 'Dashboard – Harmony HRMS',
            'pageTitle' => 'Dashboard',
            'pageDescription' => 'Monitor your organization\'s key metrics and recent activities',
            'stats' => $this->getDashboardStats(),
            'recentActivities' => $this->getRecentActivities(),
            'helpLink' => 'https://docs.harmonyhrms.com/dashboard',
            'pageId' => 'dashboard-home',
            'isFavorite' => in_array('dashboard-home', $user['favorites'] ?? [])
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

    public function updateWidget(): void
    {
        // Check authentication
        if (!SessionManager::isLoggedIn()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // CSRF is automatically verified by middleware for POST requests
        
        $widgetId = $_POST['widget_id'] ?? null;
        $configuration = $_POST['configuration'] ?? [];
        
        // Process the update...
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
}