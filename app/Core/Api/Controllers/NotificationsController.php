<?php

// File: app/Core/Api/Controllers/NotificationsController.php

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;

class NotificationsController
{
    use LoggerTrait;

    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Mark notifications as viewed
     */
    public function markAsViewed(Request $request): Response
    {
        if (! $this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();

        $this->logInfo('Notifications viewed', [
            'user' => $user['username'] ?? 'unknown',
            'count' => $user['notificationCount'] ?? 0,
        ]);

        return $this->jsonResponse(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request): Response
    {
        if (! $this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $previousCount = $user['notificationCount'] ?? 0;

        // Update session
        $user['notificationCount'] = 0;
        $this->sessionManager->set('user', $user);

        $this->logInfo('All notifications marked as read', [
            'user' => $user['username'] ?? 'unknown',
            'previousCount' => $previousCount,
        ]);

        return $this->jsonResponse([
            'success' => true,
            'count' => 0,
            'marked' => $previousCount,
        ]);
    }

    /**
     * Get notifications
     */
    public function getNotifications(Request $request): Response
    {
        if (! $this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        // Mock notifications data
        $notifications = [
            [
                'id' => 1,
                'type' => 'leave_approved',
                'icon' => 'fas fa-check',
                'color' => 'green',
                'message' => 'Your leave request for <span class="font-medium">Dec 25-27</span> has been approved',
                'time' => '10 minutes ago',
                'read' => false,
            ],
            [
                'id' => 2,
                'type' => 'new_team_member',
                'icon' => 'fas fa-user-plus',
                'color' => 'blue',
                'message' => '<span class="font-medium">Sarah Johnson</span> joined your team',
                'time' => '2 hours ago',
                'read' => false,
            ],
            [
                'id' => 3,
                'type' => 'birthday',
                'icon' => 'fas fa-birthday-cake',
                'color' => 'purple',
                'message' => 'Today is <span class="font-medium">Michael Chen\'s</span> birthday! 🎉',
                'time' => '8:00 AM',
                'read' => true,
            ],
        ];

        return $this->jsonResponse([
            'notifications' => $notifications,
            'total' => count($notifications),
            'unread' => count(array_filter($notifications, fn ($n) => ! $n['read'])),
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
