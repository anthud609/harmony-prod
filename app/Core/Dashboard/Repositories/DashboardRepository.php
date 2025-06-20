<?php

// File: app/Core/Dashboard/Repositories/DashboardRepository.php

namespace App\Core\Dashboard\Repositories;

/**
 * Simple implementation without database dependency
 */
class DashboardRepository
{
    public function getStats(int $userId): array
    {
        // Mock data for now
        return [
            'totalEmployees' => 284,
            'presentToday' => 270,
            'onLeave' => 14,
            'newApplications' => 8,
        ];
    }

    public function getUserFavorites(int $userId): array
    {
        return ['dashboard-home', 'employees-list'];
    }
}
