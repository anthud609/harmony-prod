<?php
// =============================================================================
// File: app/Core/Api/Controllers/SessionController.php (FIXED)
// =============================================================================

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
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

    public function status(Request $request): Response
    {
        $sessionId = session_id();
        $hasUser = $this->sessionManager->has('user');

        $this->logDebug('Session status check START', [
            'sessionId' => $sessionId,
            'hasUser' => $hasUser,
            'sessionData' => array_keys($_SESSION ?? []),
        ]);

        if (!$this->sessionManager->isLoggedIn()) {
            $this->logInfo('Session status check - user not logged in', [
                'sessionId' => $sessionId,
                'reason' => 'no_user_in_session',
            ]);
            
            return (new Response())->json(['error' => 'Not authenticated'], 401);
        }

        $remainingTime = $this->sessionManager->getRemainingLifetime();
        $user = $this->sessionManager->get('user');
        $lastActivity = $this->sessionManager->get('_last_activity');
        $currentTime = time();
        $elapsedTime = $lastActivity ? ($currentTime - $lastActivity) : 0;

        $this->logInfo('Session status - timing details', [
            'username' => $user['username'] ?? 'unknown',
            'remainingTime' => $remainingTime,
            'lastActivity' => $lastActivity,
            'currentTime' => $currentTime,
            'elapsedTime' => $elapsedTime,
            'sessionLifetime' => 360,
            'warningThreshold' => 60,
            'shouldShowWarning' => ($remainingTime <= 60 && $remainingTime > 0),
        ]);

        if ($remainingTime <= 0) {
            $this->logWarning('Session has expired', [
                'username' => $user['username'] ?? 'unknown',
                'lastActivity' => date('Y-m-d H:i:s', $lastActivity),
                'currentTime' => date('Y-m-d H:i:s', $currentTime),
                'elapsedSeconds' => $elapsedTime,
            ]);
        } elseif ($remainingTime <= 60) {
            $this->logWarning('Session expiring soon - warning should be shown', [
                'username' => $user['username'] ?? 'unknown',
                'remainingSeconds' => $remainingTime,
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
                'username' => $user['username'],
            ],
        ];

        $this->logDebug('Session status response', $response);

        return (new Response())->json($response);
    }

    public function extend(Request $request): Response
    {
        $this->logInfo('Session extend request received', [
            'sessionId' => session_id(),
            'user' => $this->sessionManager->get('user')['username'] ?? 'unknown',
        ]);

        if (!$this->sessionManager->isLoggedIn()) {
            $this->logWarning('Session extend failed - user not logged in');
            return (new Response())->json(['error' => 'Not authenticated'], 401);
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
            'extended' => ($afterExtend > $beforeExtend),
        ]);

        return (new Response())->json([
            'success' => true,
            'remainingTime' => $afterExtend,
            'lastActivity' => $afterActivity,
            'currentTime' => time(),
        ]);
    }
}
