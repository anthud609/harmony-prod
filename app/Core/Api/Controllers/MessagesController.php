<?php

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Message;
use App\Modules\IAM\Models\User;

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
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        
        try {
            // Mark all unread messages as read
            Message::where('recipient_id', $user['id'])
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
            
            // Update user's message count in session
            $user['messageCount'] = 0;
            $this->sessionManager->set('user', $user);

            $this->logInfo('Messages marked as read', [
                'user' => $user['username'] ?? 'unknown',
            ]);

            return $this->jsonResponse([
                'success' => true,
                'count' => 0,
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Failed to mark messages as read', [
                'error' => $e->getMessage(),
                'user' => $user['username'] ?? 'unknown'
            ]);
            
            return $this->jsonResponse(['error' => 'Failed to update messages'], 500);
        }
    }

    /**
     * Get messages
     */
    public function getMessages(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        
        try {
            // Get recent messages from database
            $messages = Message::with('sender')
                ->where('recipient_id', $user['id'])
                ->where('is_archived', false)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Format messages for frontend
            $formattedMessages = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender->full_name,
                    'preview' => $message->preview,
                    'time' => $message->time,
                    'read' => $message->is_read,
                    'url' => '/messages/' . $message->id,
                    'avatar' => [
                        'initials' => $message->sender->initials,
                        'gradient' => $message->avatar['gradient']
                    ]
                ];
            })->toArray();
            
            // Update unread count in session
            $unreadCount = Message::where('recipient_id', $user['id'])
                ->where('is_read', false)
                ->count();
            
            $user['messageCount'] = $unreadCount;
            $this->sessionManager->set('user', $user);

            return $this->jsonResponse([
                'messages' => $formattedMessages,
                'total' => count($formattedMessages),
                'unread' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Failed to fetch messages', [
                'error' => $e->getMessage(),
                'user' => $user['username'] ?? 'unknown'
            ]);
            
            return $this->jsonResponse(['error' => 'Failed to fetch messages'], 500);
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