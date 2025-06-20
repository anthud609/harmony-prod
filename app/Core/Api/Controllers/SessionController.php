<?php
// File: app/Core/Api/Controllers/SessionController.php
namespace App\Core\Api\Controllers;

use App\Core\Security\SessionManager;

class SessionController
{
    /**
     * Get session status (for AJAX)
     */
    public function status(): void
    {
        header('Content-Type: application/json');
        
        if (!SessionManager::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        $remainingTime = SessionManager::getRemainingLifetime();
        
        echo json_encode([
            'authenticated' => true,
            'remainingTime' => $remainingTime,
            'user' => [
                'name' => SessionManager::get('user')['firstName'] . ' ' . SessionManager::get('user')['lastName'],
                'username' => SessionManager::get('user')['username']
            ]
        ]);
    }
    
    /**
     * Extend session lifetime
     */
    public function extend(): void
    {
        header('Content-Type: application/json');
        
        if (!SessionManager::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Extend the session
        SessionManager::extend();
        
        echo json_encode([
            'success' => true,
            'remainingTime' => SessionManager::getRemainingLifetime()
        ]);
    }
}