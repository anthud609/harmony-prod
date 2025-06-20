<?php
// File: app/Core/Api/Controllers/SessionController.php
namespace App\Core\Api\Controllers;

use App\Core\Security\SessionManager;

class SessionController
{
    private SessionManager $sessionManager;
    
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
    
    /**
     * Get session status (for AJAX)
     */
    public function status(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->sessionManager->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        $remainingTime = $this->sessionManager->getRemainingLifetime();
        $user = $this->sessionManager->get('user');
        
        echo json_encode([
            'authenticated' => true,
            'remainingTime' => $remainingTime,
            'user' => [
                'name' => $user['firstName'] . ' ' . $user['lastName'],
                'username' => $user['username']
            ]
        ]);
    }
    
    /**
     * Extend session lifetime
     */
    public function extend(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->sessionManager->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Extend the session
        $this->sessionManager->extend();
        
        echo json_encode([
            'success' => true,
            'remainingTime' => $this->sessionManager->getRemainingLifetime()
        ]);
    }
}