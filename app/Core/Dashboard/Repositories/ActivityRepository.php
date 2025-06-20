<?php

// File: app/Core/Dashboard/Repositories/ActivityRepository.php

namespace App\Core\Dashboard\Repositories;

/**
 * Simple implementation without database dependency
 */
class ActivityRepository
{
    public function getRecentActivities(int $userId, int $limit = 10): array
    {
        // Mock data for now
        return [
            [
                'id' => 1,
                'message' => 'New employee <span class="font-medium">Sarah Johnson</span> joined the team',
                'time' => '2 hours ago',
                'icon' => 'fas fa-user-plus',
                'color' => 'green',
                'timestamp' => time() - 7200,
            ],
            [
                'id' => 2,
                'message' => 'Leave request from <span class="font-medium">Mike Chen</span> approved',
                'time' => '4 hours ago',
                'icon' => 'fas fa-check-circle',
                'color' => 'blue',
                'timestamp' => time() - 14400,
            ],
            [
                'id' => 3,
                'message' => 'Payroll processing completed for December',
                'time' => '6 hours ago',
                'icon' => 'fas fa-money-check-alt',
                'color' => 'purple',
                'timestamp' => time() - 21600,
            ],
        ];
    }
}
