<?php


// File: app/Core/Http/Middleware/MiddlewareInterface.php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}

// File: app/Core/Dashboard/Controllers/DashboardController.php (Refactored)
namespace App\Core\Dashboard\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Dashboard\Services\DashboardService;
use App\Core\Dashboard\Presenters\DashboardPresenter;

/**
 * Clean controller - only handles HTTP concerns
 */
class DashboardController
{
    private DashboardService $dashboardService;
    private DashboardPresenter $presenter;
    
    public function __construct(
        DashboardService $dashboardService,
        DashboardPresenter $presenter
    ) {
        $this->dashboardService = $dashboardService;
        $this->presenter = $presenter;
    }
    
    /**
     * Display dashboard - clean action method
     */
    public function index(Request $request): Response
    {
        // Get user from request (set by middleware)
        $user = $request->getAttribute('user');
        
        // Get dashboard data from service
        $dashboardData = $this->dashboardService->getDashboardData($user['id']);
        
        // Use presenter to format the view
        $html = $this->presenter->renderDashboard($dashboardData, $user);
        
        // Return response
        return (new Response())
            ->setContent($html)
            ->setHeader('Content-Type', 'text/html; charset=utf-8');
    }
    
    /**
     * Update widget via AJAX - clean action method
     */
    public function updateWidget(Request $request): Response
    {
        $user = $request->getAttribute('user');
        $widgetId = $request->getPost('widget_id');
        $configuration = $request->getPost('configuration', []);
        
        try {
            $result = $this->dashboardService->updateWidget(
                $user['id'],
                $widgetId,
                $configuration
            );
            
            return (new Response())->json($result);
            
        } catch (\InvalidArgumentException $e) {
            return (new Response())->json(
                ['error' => $e->getMessage()],
                400
            );
        } catch (\Exception $e) {
            return (new Response())->json(
                ['error' => 'Internal server error'],
                500
            );
        }
    }
}