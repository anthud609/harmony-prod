<?php

// File: app/Core/Dashboard/Controllers/DashboardController.php

namespace App\Core\Dashboard\Controllers;

use App\Core\Dashboard\Services\DashboardService;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;

class DashboardController
{
    protected LayoutManager $layoutManager;
    protected SessionManager $sessionManager;
    protected DashboardService $dashboardService;

    public function __construct(
        LayoutManager $layoutManager,
        SessionManager $sessionManager,
        DashboardService $dashboardService
    ) {
        $this->layoutManager = $layoutManager;
        $this->sessionManager = $sessionManager;
        $this->dashboardService = $dashboardService;
    }

    public function index(): void
    {
        // Check if user is logged in using injected SessionManager
        if (! $this->sessionManager->isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        // Get user from session
        $user = $this->sessionManager->getUser();

        // Prepare data for the view using service
        $data = [
            'title' => 'Dashboard – Harmony HRMS',
            'pageTitle' => 'Dashboard',
            'pageDescription' => 'Monitor your organization\'s key metrics and recent activities',
            'stats' => $this->dashboardService->getDashboardStats(),
            'recentActivities' => $this->dashboardService->getRecentActivities(),
            'helpLink' => 'https://docs.harmonyhrms.com/dashboard',
            'pageId' => 'dashboard-home',
            'isFavorite' => in_array('dashboard-home', $user['favorites'] ?? []),
        ];

        // Set breadcrumbs
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Dashboard'],
        ];

        // Set page actions
        $pageActions = $this->getPageActions();

        // Render the view with the layout
        $this->layoutManager
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/dashboard.php');
    }

    protected function getPageActions(): array
    {
        return [
            [
                'label' => 'Export Report',
                'icon' => 'fas fa-download',
                'variant' => 'secondary',
                'onclick' => 'exportDashboard(); return false;',
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
                        'onclick' => 'refreshDashboard(); return false;',
                    ],
                    [
                        'label' => 'Configure Widgets',
                        'icon' => 'fas fa-cog',
                        'url' => '/dashboard/configure',
                    ],
                    'divider',
                    [
                        'label' => 'Reset to Default',
                        'icon' => 'fas fa-undo',
                        'onclick' => 'resetDashboard(); return false;',
                        'danger' => true,
                    ],
                ],
            ],
            [
                'label' => 'Add Widget',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
                'onclick' => 'openAddWidgetModal(); return false;',
            ],
        ];
    }

    public function updateWidget(): void
    {
        // Check authentication
        if (! $this->sessionManager->isLoggedIn()) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // CSRF is automatically verified by middleware for POST requests

        $widgetId = $_POST['widget_id'] ?? null;
        $configuration = $_POST['configuration'] ?? [];

        // Process the update using service
        $result = $this->dashboardService->updateWidget($widgetId, $configuration);

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
