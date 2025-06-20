<?php
// File: app/Core/Api/Controllers/SearchController.php
namespace App\Core\Api\Controllers;

use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Core\Http\Request;
use App\Core\Http\Response;

class SearchController
{
    use LoggerTrait;
    
    private SessionManager $sessionManager;
    
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Global search endpoint
     */
    public function search(Request $request): Response
    {
        // Check authentication
        if (!$this->sessionManager->isLoggedIn()) {
            $this->logWarning('Unauthenticated search attempt');
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }
        
        // Get search parameters
        $data = json_decode($request->getPost('body', '{}'), true);
        $query = $data['query'] ?? '';
        $limit = min($data['limit'] ?? 10, 50); // Cap at 50 results
        
        if (empty($query) || strlen($query) < 2) {
            return $this->jsonResponse(['error' => 'Query too short'], 400);
        }
        
        $this->logInfo('Search performed', [
            'query' => $query,
            'user' => $this->sessionManager->get('user')['username'] ?? 'unknown'
        ]);
        
        // Perform search
        $results = $this->performSearch($query, $limit);
        
        return $this->jsonResponse($results);
    }
    
    /**
     * Perform the actual search
     */
    private function performSearch(string $query, int $limit): array
    {
        $results = [];
        $queryLower = strtolower($query);
        
        // Search employees (mock data)
        $employees = [
            ['name' => 'Sarah Johnson', 'role' => 'Software Engineer', 'department' => 'Engineering'],
            ['name' => 'Mike Chen', 'role' => 'Product Manager', 'department' => 'Product'],
            ['name' => 'Alice Williams', 'role' => 'HR Manager', 'department' => 'Human Resources'],
            ['name' => 'Bob Smith', 'role' => 'Finance Director', 'department' => 'Finance'],
            ['name' => 'Charlie Brown', 'role' => 'Sales Representative', 'department' => 'Sales'],
        ];
        
        foreach ($employees as $employee) {
            if (stripos($employee['name'], $queryLower) !== false || 
                stripos($employee['role'], $queryLower) !== false ||
                stripos($employee['department'], $queryLower) !== false) {
                $results[] = [
                    'title' => $employee['name'],
                    'description' => $employee['role'] . ' - ' . $employee['department'],
                    'icon' => 'fas fa-user',
                    'color' => 'indigo',
                    'url' => '/employees/view/' . urlencode($employee['name']),
                    'type' => 'employee'
                ];
            }
        }
        
        // Search documents (mock data)
        $documents = [
            ['title' => 'Employee Handbook', 'type' => 'PDF', 'updated' => '2024-01-15'],
            ['title' => 'Vacation Policy', 'type' => 'Document', 'updated' => '2024-02-01'],
            ['title' => 'Code of Conduct', 'type' => 'PDF', 'updated' => '2023-12-10'],
            ['title' => 'Benefits Guide', 'type' => 'Document', 'updated' => '2024-01-20'],
        ];
        
        foreach ($documents as $doc) {
            if (stripos($doc['title'], $queryLower) !== false) {
                $results[] = [
                    'title' => $doc['title'],
                    'description' => $doc['type'] . ' - Updated ' . $doc['updated'],
                    'icon' => 'fas fa-file-alt',
                    'color' => 'green',
                    'url' => '/documents/view/' . urlencode($doc['title']),
                    'type' => 'document'
                ];
            }
        }
        
        // Search actions/pages
        $actions = [
            ['title' => 'Add New Employee', 'action' => 'add-employee', 'permission' => 'hr'],
            ['title' => 'Request Leave', 'action' => 'request-leave', 'permission' => 'all'],
            ['title' => 'View Payroll', 'action' => 'view-payroll', 'permission' => 'finance'],
            ['title' => 'Generate Reports', 'action' => 'generate-reports', 'permission' => 'manager'],
        ];
        
        foreach ($actions as $action) {
            if (stripos($action['title'], $queryLower) !== false) {
                $results[] = [
                    'title' => $action['title'],
                    'description' => 'Quick action',
                    'icon' => 'fas fa-bolt',
                    'color' => 'purple',
                    'action' => $action['action'],
                    'type' => 'action'
                ];
            }
        }
        
        // Limit results
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Helper to create JSON response
     */
    private function jsonResponse($data, int $status = 200): Response
    {
        $response = new Response();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
}