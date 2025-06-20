<?php
namespace App\Core\Dashboard\Controllers;

use App\Core\Layout\LayoutManager;

class DashboardController
{
    protected LayoutManager $layout;
    
    public function __construct()
    {
        $this->layout = new LayoutManager();
    }
    
    public function index(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        // Prepare data for the view
        $data = [
            'title' => 'Dashboard – Harmony HRMS',
            'stats' => $this->getDashboardStats(),
            'recentActivities' => $this->getRecentActivities()
        ];
        
        // Render the view with the layout
        $this->layout
            ->setLayout('main')
            ->with($data)
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
}