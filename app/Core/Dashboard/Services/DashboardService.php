<?php
// File: app/Core/Dashboard/Services/DashboardService.php (Enhanced)
namespace App\Core\Dashboard\Services;

use App\Core\Dashboard\Repositories\DashboardRepository;
use App\Core\Dashboard\Repositories\ActivityRepository;
use App\Core\Dashboard\Repositories\WidgetRepository;

/**
 * Business logic layer - handles complex operations
 */
class DashboardService
{
    private DashboardRepository $dashboardRepo;
    private ActivityRepository $activityRepo;
    private WidgetRepository $widgetRepo;
    
    public function __construct(
        DashboardRepository $dashboardRepo,
        ActivityRepository $activityRepo,
        WidgetRepository $widgetRepo
    ) {
        $this->dashboardRepo = $dashboardRepo;
        $this->activityRepo = $activityRepo;
        $this->widgetRepo = $widgetRepo;
    }
    
    /**
     * Get complete dashboard data for a user
     */
    public function getDashboardData(int $userId): array
    {
        return [
            'stats' => $this->dashboardRepo->getStats($userId),
            'activities' => $this->activityRepo->getRecentActivities($userId, 10),
            'widgets' => $this->widgetRepo->getUserWidgets($userId),
            'favorites' => $this->dashboardRepo->getUserFavorites($userId)
        ];
    }
    
    /**
     * Update widget configuration with validation
     */
    public function updateWidget(int $userId, ?string $widgetId, array $configuration): array
    {
        if (!$widgetId) {
            throw new \InvalidArgumentException('Widget ID is required');
        }
        
        // Validate user owns the widget
        if (!$this->widgetRepo->userOwnsWidget($userId, $widgetId)) {
            throw new \InvalidArgumentException('Widget not found');
        }
        
        // Validate configuration
        $this->validateWidgetConfiguration($widgetId, $configuration);
        
        // Update widget
        $success = $this->widgetRepo->updateConfiguration($widgetId, $configuration);
        
        return [
            'success' => $success,
            'widgetId' => $widgetId,
            'configuration' => $configuration
        ];
    }
    
    private function validateWidgetConfiguration(string $widgetId, array $configuration): void
    {
        // Business rules for widget configuration
        $widgetType = $this->widgetRepo->getWidgetType($widgetId);
        
        switch ($widgetType) {
            case 'chart':
                if (!isset($configuration['chartType'])) {
                    throw new \InvalidArgumentException('Chart type is required');
                }
                break;
            case 'stat':
                if (!isset($configuration['metric'])) {
                    throw new \InvalidArgumentException('Metric is required');
                }
                break;
        }
    }
}