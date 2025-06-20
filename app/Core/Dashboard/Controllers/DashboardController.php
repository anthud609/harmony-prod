<?php
// =============================================================================
// File: app/Core/Dashboard/Controllers/DashboardController.php (FIXED)
// =============================================================================

namespace App\Core\Dashboard\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
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

    public function index(Request $request): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            return (new Response())->redirect('/login');
        }

        // Get user from session
        $user = $this->sessionManager->getUser();

        // Prepare data for the view
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

        // Set breadcrumbs and actions
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Dashboard'],
        ];
        $pageActions = $this->getPageActions();

        // Capture layout output
        ob_start();
        $this->layoutManager
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/dashboard.php');
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    public function updateWidget(Request $request): Response
    {
        // Check authentication
        if (!$this->sessionManager->isLoggedIn()) {
            return (new Response())->json(['error' => 'Unauthorized'], 401);
        }

        $widgetId = $request->getPost('widget_id');
        $configuration = $request->getPost('configuration', []);

        // Process the update using service
        $result = $this->dashboardService->updateWidget($widgetId, $configuration);

        return (new Response())->json($result);
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
}