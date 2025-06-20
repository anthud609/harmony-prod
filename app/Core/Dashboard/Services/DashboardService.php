<?php

// File: app/Core/Dashboard/Services/DashboardService.php

namespace App\Core\Dashboard\Services;

use App\Core\Dashboard\Repositories\ActivityRepository;
use App\Core\Dashboard\Repositories\DashboardRepository;
use App\Core\Dashboard\Repositories\WidgetRepository;

/**
 * Simplified business logic layer
 */
class DashboardService
{
    private ?DashboardRepository $dashboardRepo = null;
    private ?ActivityRepository $activityRepo = null;
    private ?WidgetRepository $widgetRepo = null;

    public function __construct(
        DashboardRepository $dashboardRepo = null,
        ActivityRepository $activityRepo = null,
        WidgetRepository $widgetRepo = null
    ) {
        $this->dashboardRepo = $dashboardRepo ?? new DashboardRepository();
        $this->activityRepo = $activityRepo ?? new ActivityRepository();
        $this->widgetRepo = $widgetRepo ?? new WidgetRepository();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        // Using simplified method without userId for now
        return $this->dashboardRepo->getStats(1);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(): array
    {
        return $this->activityRepo->getRecentActivities(1, 10);
    }

    /**
     * Update widget configuration
     */
    public function updateWidget(?string $widgetId, array $configuration): array
    {
        if (! $widgetId) {
            throw new \InvalidArgumentException('Widget ID is required');
        }

        // Simplified validation
        if (! $this->widgetRepo->userOwnsWidget(1, $widgetId)) {
            throw new \InvalidArgumentException('Widget not found');
        }

        // Update widget
        $success = $this->widgetRepo->updateConfiguration($widgetId, $configuration);

        return [
            'success' => $success,
            'widgetId' => $widgetId,
            'configuration' => $configuration,
        ];
    }
}
