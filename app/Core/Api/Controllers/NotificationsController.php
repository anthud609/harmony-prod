<?php

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Notification;

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
        if (!$this->sessionManager->isLoggedIn()) {
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
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        
        try {
            // Mark all unread notifications as read
            Notification::where('user_id', $user['id'])
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
            
            // Update notification count in session
            $user['notificationCount'] = 0;
            $this->sessionManager->set('user', $user);

            $this->logInfo('All notifications marked as read', [
                'user' => $user['username'] ?? 'unknown',
            ]);

            return $this->jsonResponse([
                'success' => true,
                'count' => 0,
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Failed to mark notifications as read', [
                'error' => $e->getMessage(),
                'user' => $user['username'] ?? 'unknown'
            ]);
            
            return $this->jsonResponse(['error' => 'Failed to update notifications'], 500);
        }
    }

    /**
     * Get notifications
     */
    public function getNotifications(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        
        try {
            // Get recent notifications from database
            $notifications = Notification::where('user_id', $user['id'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            // Format notifications for frontend
            $formattedNotifications = $notifications->map(function ($notification) {
                $displayData = $notification->display_data;
                
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'icon' => $displayData['icon'],
                    'color' => $displayData['color'],
                    'message' => $notification->message,
                    'time' => $notification->time,
                    'read' => $notification->is_read,
                ];
            })->toArray();
            
            // Update unread count in session
            $unreadCount = Notification::where('user_id', $user['id'])
                ->where('is_read', false)
                ->count();
            
            $user['notificationCount'] = $unreadCount;
            $this->sessionManager->set('user', $user);

            return $this->jsonResponse([
                'notifications' => $formattedNotifications,
                'total' => count($formattedNotifications),
                'unread' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Failed to fetch notifications', [
                'error' => $e->getMessage(),
                'user' => $user['username'] ?? 'unknown'
            ]);
            
            return $this->jsonResponse(['error' => 'Failed to fetch notifications'], 500);
        }
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