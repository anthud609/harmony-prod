<?php

// File: app/Core/Api/Controllers/MessagesController.php

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;

class MessagesController
{
    use LoggerTrait;

    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request): Response
    {
        if (! $this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();

        // In a real app, update database
        // For now, update session
        $user['messageCount'] = 0;
        $this->sessionManager->set('user', $user);

        $this->logInfo('Messages marked as read', [
            'user' => $user['username'] ?? 'unknown',
        ]);

        return $this->jsonResponse([
            'success' => true,
            'count' => 0,
        ]);
    }

    /**
     * Get messages
     */
    public function getMessages(Request $request): Response
    {
        if (! $this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        // Mock messages data
        $messages = [
            [
                'id' => 1,
                'sender' => 'Jane Doe',
                'preview' => 'Hey, can you review the Q4 report before the meeting tomorrow?',
                'time' => '5m ago',
                'read' => false,
                'avatar' => [
                    'initials' => 'JD',
                    'gradient' => 'from-green-400 to-blue-500',
                ],
            ],
            [
                'id' => 2,
                'sender' => 'Mark Robinson',
                'preview' => 'The new employee onboarding process has been updated. Please check...',
                'time' => '1h ago',
                'read' => false,
                'avatar' => [
                    'initials' => 'MR',
                    'gradient' => 'from-purple-400 to-pink-500',
                ],
            ],
        ];

        return $this->jsonResponse([
            'messages' => $messages,
            'total' => count($messages),
            'unread' => count(array_filter($messages, fn ($m) => ! $m['read'])),
        ]);
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
