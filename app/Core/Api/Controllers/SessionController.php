<?php
// File: app/Core/Api/Controllers/SessionController.php
namespace App\Core\Api\Controllers;

use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;

class SessionController
{
    use LoggerTrait;
    
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
        
        $sessionId = session_id();
        $hasUser = $this->sessionManager->has('user');
        
        $this->logDebug('Session status check START', [
            'sessionId' => $sessionId,
            'hasUser' => $hasUser,
            'sessionData' => array_keys($_SESSION ?? [])
        ]);
        
        if (!$this->sessionManager->isLoggedIn()) {
            $this->logInfo('Session status check - user not logged in', [
                'sessionId' => $sessionId,
                'reason' => 'no_user_in_session'
            ]);
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        $remainingTime = $this->sessionManager->getRemainingLifetime();
        $user = $this->sessionManager->get('user');
        $lastActivity = $this->sessionManager->get('_last_activity');
        $currentTime = time();
        $elapsedTime = $lastActivity ? ($currentTime - $lastActivity) : 0;
        
        // Log detailed timing information
        $this->logInfo('Session status - timing details', [
            'username' => $user['username'] ?? 'unknown',
            'remainingTime' => $remainingTime,
            'lastActivity' => $lastActivity,
            'currentTime' => $currentTime,
            'elapsedTime' => $elapsedTime,
            'sessionLifetime' => 360,
            'warningThreshold' => 60,
            'shouldShowWarning' => ($remainingTime <= 60 && $remainingTime > 0)
        ]);
        
        // Additional debug info for troubleshooting
        if ($remainingTime <= 0) {
            $this->logWarning('Session has expired', [
                'username' => $user['username'] ?? 'unknown',
                'lastActivity' => date('Y-m-d H:i:s', $lastActivity),
                'currentTime' => date('Y-m-d H:i:s', $currentTime),
                'elapsedSeconds' => $elapsedTime
            ]);
        } elseif ($remainingTime <= 60) {
            $this->logWarning('Session expiring soon - warning should be shown', [
                'username' => $user['username'] ?? 'unknown',
                'remainingSeconds' => $remainingTime
            ]);
        }
        
        $response = [
            'authenticated' => true,
            'remainingTime' => $remainingTime,
            'lastActivity' => $lastActivity,
            'currentTime' => $currentTime,
            'elapsedTime' => $elapsedTime,
            'sessionLifetime' => 360,
            'user' => [
                'name' => $user['firstName'] . ' ' . $user['lastName'],
                'username' => $user['username']
            ]
        ];
        
        $this->logDebug('Session status response', $response);
        
        echo json_encode($response);
    }
    
    /**
     * Extend session lifetime
     */
    public function extend(): void
    {
        header('Content-Type: application/json');
        
        $this->logInfo('Session extend request received', [
            'sessionId' => session_id(),
            'user' => $this->sessionManager->get('user')['username'] ?? 'unknown'
        ]);
        
        if (!$this->sessionManager->isLoggedIn()) {
            $this->logWarning('Session extend failed - user not logged in');
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
        
        // Get before and after times for logging
        $beforeExtend = $this->sessionManager->getRemainingLifetime();
        $beforeActivity = $this->sessionManager->get('_last_activity');
        
        // Extend the session
        $this->sessionManager->extend();
        
        $afterExtend = $this->sessionManager->getRemainingLifetime();
        $afterActivity = $this->sessionManager->get('_last_activity');
        
        $this->logInfo('Session extended successfully', [
            'username' => $this->sessionManager->get('user')['username'] ?? 'unknown',
            'beforeRemaining' => $beforeExtend,
            'afterRemaining' => $afterExtend,
            'beforeActivity' => date('Y-m-d H:i:s', $beforeActivity),
            'afterActivity' => date('Y-m-d H:i:s', $afterActivity),
            'extended' => ($afterExtend > $beforeExtend)
        ]);
        
        echo json_encode([
            'success' => true,
            'remainingTime' => $afterExtend,
            'lastActivity' => $afterActivity,
            'currentTime' => time()
        ]);
    }
}