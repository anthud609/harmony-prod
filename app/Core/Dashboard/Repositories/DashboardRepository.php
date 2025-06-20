<?php

// File: app/Core/Dashboard/Repositories/DashboardRepository.php
namespace App\Core\Dashboard\Repositories;

/**
 * Data access layer - handles database queries
 */
class DashboardRepository
{
    private \PDO $db;
    
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }
    
    public function getStats(int $userId): array
    {
        // In real app, this would be database queries
        return [
            'totalEmployees' => $this->getTotalEmployees($userId),
            'presentToday' => $this->getPresentToday($userId),
            'onLeave' => $this->getOnLeave($userId),
            'newApplications' => $this->getNewApplications($userId)
        ];
    }
    
    public function getUserFavorites(int $userId): array
    {
        // Real implementation would query database
        return ['dashboard-home', 'employees-list'];
    }
    
    private function getTotalEmployees(int $userId): int
    {
        // Real query: SELECT COUNT(*) FROM employees WHERE organization_id = ?
        return 284;
    }
    
    private function getPresentToday(int $userId): int
    {
        // Real query: SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'present'
        return 270;
    }
    
    private function getOnLeave(int $userId): int
    {
        // Real query: SELECT COUNT(*) FROM leave_requests WHERE status = 'approved' AND ? BETWEEN start_date AND end_date
        return 14;
    }
    
    private function getNewApplications(int $userId): int
    {
        // Real query: SELECT COUNT(*) FROM applications WHERE status = 'pending'
        return 8;
    }
}
