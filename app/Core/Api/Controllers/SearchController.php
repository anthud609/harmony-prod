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
    
    // Configuration
    private const MIN_QUERY_LENGTH = 2;
    private const MAX_QUERY_LENGTH = 100;
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 50;
    private const SEARCH_TIMEOUT = 5; // seconds
    
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Global search endpoint
     */
    public function search(Request $request): Response
    {
        // Start timing
        $startTime = microtime(true);
        
        try {
            // Check authentication
            if (!$this->sessionManager->isLoggedIn()) {
                $this->logWarning('Unauthenticated search attempt');
                return $this->jsonResponse(['error' => 'Not authenticated'], 401);
            }
            
            // Parse request body
            $body = $request->getPost('body', '');
            if (empty($body)) {
                // Try reading raw input for JSON requests
                $rawInput = file_get_contents('php://input');
                if (!empty($rawInput)) {
                    $body = $rawInput;
                }
            }
            
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse([
                    'error' => 'Invalid JSON',
                    'message' => 'Request body must be valid JSON'
                ], 400);
            }
            
            // Validate and sanitize input
            $query = trim($data['query'] ?? '');
            $limit = min(max((int)($data['limit'] ?? self::DEFAULT_LIMIT), 1), self::MAX_LIMIT);
            
            // Validate query length
            if (strlen($query) < self::MIN_QUERY_LENGTH) {
                return $this->jsonResponse([
                    'error' => 'Query too short',
                    'message' => sprintf('Query must be at least %d characters', self::MIN_QUERY_LENGTH)
                ], 400);
            }
            
            if (strlen($query) > self::MAX_QUERY_LENGTH) {
                return $this->jsonResponse([
                    'error' => 'Query too long',
                    'message' => sprintf('Query must not exceed %d characters', self::MAX_QUERY_LENGTH)
                ], 400);
            }
            
            $this->logInfo('Search performed', [
                'query' => $query,
                'limit' => $limit,
                'user' => $this->sessionManager->get('user')['username'] ?? 'unknown'
            ]);
            
            // Perform search with timeout protection
            $results = $this->performSearchWithTimeout($query, $limit);
            
            // Log performance
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            $this->logDebug('Search completed', [
                'query' => $query,
                'resultCount' => count($results),
                'duration_ms' => $duration
            ]);
            
            return $this->jsonResponse($results);
            
        } catch (\Exception $e) {
            $this->logError('Search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->jsonResponse([
                'error' => 'Search failed',
                'message' => 'An error occurred while searching. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Perform search with timeout protection
     */
    private function performSearchWithTimeout(string $query, int $limit): array
    {
        $timeout = self::SEARCH_TIMEOUT;
        $startTime = time();
        
        try {
            // In a real application, you might use:
            // - Elasticsearch, Algolia, or other search services
            // - Database full-text search
            // - Microservice architecture with circuit breakers
            
            // For now, we'll use the mock search with timeout checks
            $results = $this->performSearch($query, $limit);
            
            // Check if we've exceeded timeout
            if ((time() - $startTime) > $timeout) {
                $this->logWarning('Search timeout exceeded', [
                    'query' => $query,
                    'elapsed' => time() - $startTime
                ]);
                
                throw new \Exception('Search timeout');
            }
            
            return $results;
            
        } catch (\Exception $e) {
            // Log the timeout or error
            $this->logError('Search execution failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            // Return partial results or empty array
            return [];
        }
    }
    
    /**
     * Perform the actual search
     */
    private function performSearch(string $query, int $limit): array
    {
        $results = [];
        $queryLower = strtolower($query);
        
        // Get user for permission checking
        $user = $this->sessionManager->getUser();
        $userRole = $user['role'] ?? 'user';
        
        // Search employees (with permission check)
        if ($this->canSearchEmployees($userRole)) {
            $employeeResults = $this->searchEmployees($queryLower, $limit);
            $results = array_merge($results, $employeeResults);
        }
        
        // Search documents
        $documentResults = $this->searchDocuments($queryLower, $limit);
        $results = array_merge($results, $documentResults);
        
        // Search actions
        $actionResults = $this->searchActions($queryLower, $userRole, $limit);
        $results = array_merge($results, $actionResults);
        
        // Score and sort results by relevance
        $results = $this->scoreAndSortResults($results, $queryLower);
        
        // Limit results
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Search employees
     */
    private function searchEmployees(string $query, int $limit): array
    {
        // In production, this would query a database
        $employees = [
            ['name' => 'Sarah Johnson', 'role' => 'Software Engineer', 'department' => 'Engineering', 'email' => 'sarah.j@company.com'],
            ['name' => 'Mike Chen', 'role' => 'Product Manager', 'department' => 'Product', 'email' => 'mike.c@company.com'],
            ['name' => 'Alice Williams', 'role' => 'HR Manager', 'department' => 'Human Resources', 'email' => 'alice.w@company.com'],
            ['name' => 'Bob Smith', 'role' => 'Finance Director', 'department' => 'Finance', 'email' => 'bob.s@company.com'],
            ['name' => 'Charlie Brown', 'role' => 'Sales Representative', 'department' => 'Sales', 'email' => 'charlie.b@company.com'],
        ];
        
        $results = [];
        foreach ($employees as $employee) {
            $score = $this->calculateRelevance($query, [
                $employee['name'],
                $employee['role'],
                $employee['department'],
                $employee['email']
            ]);
            
            if ($score > 0) {
                $results[] = [
                    'title' => $employee['name'],
                    'description' => $employee['role'] . ' - ' . $employee['department'],
                    'icon' => 'fas fa-user',
                    'color' => 'indigo',
                    'url' => '/employees/view/' . urlencode($employee['name']),
                    'type' => 'employee',
                    'score' => $score
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Search documents
     */
    private function searchDocuments(string $query, int $limit): array
    {
        // In production, this would use a document search service
        $documents = [
            ['title' => 'Employee Handbook', 'type' => 'PDF', 'updated' => '2024-01-15', 'tags' => ['policy', 'guidelines', 'rules']],
            ['title' => 'Vacation Policy', 'type' => 'Document', 'updated' => '2024-02-01', 'tags' => ['leave', 'time off', 'holiday']],
            ['title' => 'Code of Conduct', 'type' => 'PDF', 'updated' => '2023-12-10', 'tags' => ['ethics', 'behavior', 'standards']],
            ['title' => 'Benefits Guide', 'type' => 'Document', 'updated' => '2024-01-20', 'tags' => ['health', 'insurance', 'retirement']],
        ];
        
        $results = [];
        foreach ($documents as $doc) {
            $score = $this->calculateRelevance($query, array_merge(
                [$doc['title']],
                $doc['tags']
            ));
            
            if ($score > 0) {
                $results[] = [
                    'title' => $doc['title'],
                    'description' => $doc['type'] . ' - Updated ' . $doc['updated'],
                    'icon' => 'fas fa-file-alt',
                    'color' => 'green',
                    'url' => '/documents/view/' . urlencode($doc['title']),
                    'type' => 'document',
                    'score' => $score
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Search actions based on user permissions
     */
    private function searchActions(string $query, string $userRole, int $limit): array
    {
        $actions = [
            ['title' => 'Add New Employee', 'action' => 'add-employee', 'permission' => 'hr', 'tags' => ['create', 'new', 'staff']],
            ['title' => 'Request Leave', 'action' => 'request-leave', 'permission' => 'all', 'tags' => ['vacation', 'time off', 'absence']],
            ['title' => 'View Payroll', 'action' => 'view-payroll', 'permission' => 'finance', 'tags' => ['salary', 'payment', 'wages']],
            ['title' => 'Generate Reports', 'action' => 'generate-reports', 'permission' => 'manager', 'tags' => ['analytics', 'data', 'statistics']],
        ];
        
        $results = [];
        foreach ($actions as $action) {
            // Check permissions
            if (!$this->hasPermission($userRole, $action['permission'])) {
                continue;
            }
            
            $score = $this->calculateRelevance($query, array_merge(
                [$action['title']],
                $action['tags']
            ));
            
            if ($score > 0) {
                $results[] = [
                    'title' => $action['title'],
                    'description' => 'Quick action',
                    'icon' => 'fas fa-bolt',
                    'color' => 'purple',
                    'action' => $action['action'],
                    'type' => 'action',
                    'score' => $score
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Calculate relevance score for search results
     */
    private function calculateRelevance(string $query, array $fields): float
    {
        $score = 0;
        $query = strtolower($query);
        $queryWords = explode(' ', $query);
        
        foreach ($fields as $field) {
            $fieldLower = strtolower($field);
            
            // Exact match
            if ($fieldLower === $query) {
                $score += 10;
            }
            // Contains full query
            elseif (strpos($fieldLower, $query) !== false) {
                $score += 5;
            }
            // Contains all query words
            else {
                $allWordsFound = true;
                foreach ($queryWords as $word) {
                    if (strpos($fieldLower, $word) === false) {
                        $allWordsFound = false;
                        break;
                    }
                }
                if ($allWordsFound) {
                    $score += 3;
                }
                // Contains some query words
                else {
                    foreach ($queryWords as $word) {
                        if (strpos($fieldLower, $word) !== false) {
                            $score += 1;
                        }
                    }
                }
            }
        }
        
        return $score;
    }
    
    /**
     * Sort results by relevance score
     */
    private function scoreAndSortResults(array $results, string $query): array
    {
        // Sort by score descending
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Remove score from results
        return array_map(function($result) {
            unset($result['score']);
            return $result;
        }, $results);
    }
    
    /**
     * Check if user can search employees
     */
    private function canSearchEmployees(string $role): bool
    {
        // In production, use proper permission system
        return in_array($role, ['admin', 'hr', 'manager']);
    }
    
    /**
     * Check if user has permission for action
     */
    private function hasPermission(string $userRole, string $requiredPermission): bool
    {
        if ($requiredPermission === 'all') {
            return true;
        }
        
        // Simple role-based permission check
        $rolePermissions = [
            'admin' => ['hr', 'finance', 'manager'],
            'hr' => ['hr'],
            'manager' => ['manager'],
            'finance' => ['finance']
        ];
        
        $userPermissions = $rolePermissions[$userRole] ?? [];
        return in_array($requiredPermission, $userPermissions);
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