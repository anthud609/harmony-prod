<?php

// File: app/Core/Dashboard/Repositories/WidgetRepository.php

namespace App\Core\Dashboard\Repositories;

/**
 * Simple implementation without database dependency
 */
class WidgetRepository
{
    public function getUserWidgets(int $userId): array
    {
        // Mock data for now
        return [
            [
                'id' => 'widget-1',
                'type' => 'stats',
                'position' => 1,
                'configuration' => ['metric' => 'employees'],
            ],
            [
                'id' => 'widget-2',
                'type' => 'chart',
                'position' => 2,
                'configuration' => ['chartType' => 'line', 'metric' => 'attendance'],
            ],
        ];
    }

    public function userOwnsWidget(int $userId, string $widgetId): bool
    {
        // For now, assume user owns all widgets
        return true;
    }

    public function updateConfiguration(string $widgetId, array $configuration): bool
    {
        // Mock success
        return true;
    }

    public function getWidgetType(string $widgetId): string
    {
        // Mock widget types
        $types = [
            'widget-1' => 'stat',
            'widget-2' => 'chart',
        ];

        return $types[$widgetId] ?? 'unknown';
    }
}
