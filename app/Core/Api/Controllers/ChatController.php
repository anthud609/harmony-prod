<?php
// File: app/Core/Api/Controllers/ChatController.php

namespace App\Core\Api\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Chat;
use App\Modules\IAM\Models\Message;
use App\Modules\IAM\Models\ChatParticipant;
use App\Modules\IAM\Models\MessageReaction;
use App\Modules\IAM\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class ChatController
{
    use LoggerTrait;

    private SessionManager $sessionManager;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $body = json_decode($request->getPost('body', '{}'), true);
            $chatId = $body['chatId'] ?? null;
            $messageText = trim($body['message'] ?? '');
            $replyToId = $body['replyToId'] ?? null;
            $attachments = $body['attachments'] ?? [];

            if (!$chatId || !$messageText) {
                return $this->jsonResponse(['error' => 'Invalid request'], 400);
            }

            // Verify user is participant
            $chat = Chat::find($chatId);
            if (!$chat || !$chat->hasParticipant($userId)) {
                return $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            DB::beginTransaction();

            // Create message
            $message = Message::create([
                'chat_id' => $chatId,
                'sender_id' => $userId,
                'reply_to_id' => $replyToId,
                'body' => $messageText,
                'type' => 'text',
                'delivered_at' => now(),
            ]);

            // Handle attachments if any
            foreach ($attachments as $attachment) {
                $message->attachments()->create([
                    'type' => $attachment['type'] ?? 'file',
                    'name' => $attachment['name'],
                    'url' => $attachment['url'],
                    'size' => $attachment['size'] ?? 0,
                    'mime_type' => $attachment['mimeType'] ?? null,
                ]);
            }

            // Update chat's last message
            $chat->update([
                'last_message_id' => $message->id,
                'last_message_at' => now(),
            ]);

            // Update unread counts for other participants
            ChatParticipant::where('chat_id', $chatId)
                ->where('user_id', '!=', $userId)
                ->increment('unread_count');

            DB::commit();

            // Load relationships for response
            $message->load(['sender', 'replyTo', 'attachments']);

            $this->logInfo('Message sent', [
                'chatId' => $chatId,
                'userId' => $userId,
                'messageId' => $message->id,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => $this->formatMessage($message, $userId),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Failed to send message', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Create a new chat
     */
    public function createChat(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $body = json_decode($request->getPost('body', '{}'), true);
            $type = $body['type'] ?? 'direct';
            $participantIds = $body['participants'] ?? [];
            $name = $body['name'] ?? null;
            $description = $body['description'] ?? null;

            // Validate participants
            if (empty($participantIds)) {
                return $this->jsonResponse(['error' => 'No participants specified'], 400);
            }

            // For direct chats, ensure only 2 participants
            if ($type === 'direct' && count($participantIds) !== 1) {
                return $this->jsonResponse(['error' => 'Direct chats must have exactly 2 participants'], 400);
            }

            // Add current user to participants
            if (!in_array($userId, $participantIds)) {
                $participantIds[] = $userId;
            }

            DB::beginTransaction();

            // Check if direct chat already exists
            if ($type === 'direct') {
                $existingChat = $this->findExistingDirectChat($userId, $participantIds[0]);
                if ($existingChat) {
                    DB::commit();
                    return $this->jsonResponse([
                        'success' => true,
                        'chatId' => $existingChat->id,
                        'existing' => true,
                    ]);
                }
            }

            // Create chat
            $chat = Chat::create([
                'type' => $type,
                'name' => $name,
                'description' => $description,
                'created_by' => $userId,
            ]);

            // Add participants
            foreach ($participantIds as $participantId) {
                $chat->addParticipant(
                    $participantId,
                    $participantId === $userId ? 'admin' : 'member'
                );
            }

            DB::commit();

            $this->logInfo('Chat created', [
                'chatId' => $chat->id,
                'type' => $type,
                'createdBy' => $userId,
            ]);

            return $this->jsonResponse([
                'success' => true,
                'chatId' => $chat->id,
                'existing' => false,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Failed to create chat', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to create chat'], 500);
        }
    }

    /**
     * Add reaction to message
     */
    public function addReaction(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $body = json_decode($request->getPost('body', '{}'), true);
            $messageId = $body['messageId'] ?? null;
            $emoji = $body['emoji'] ?? null;

            if (!$messageId || !$emoji) {
                return $this->jsonResponse(['error' => 'Invalid request'], 400);
            }

            // Verify user has access to message
            $message = Message::find($messageId);
            if (!$message || !$message->chat->hasParticipant($userId)) {
                return $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            // Toggle reaction
            $existingReaction = MessageReaction::where('message_id', $messageId)
                ->where('user_id', $userId)
                ->where('emoji', $emoji)
                ->first();

            if ($existingReaction) {
                $existingReaction->delete();
                $added = false;
            } else {
                MessageReaction::create([
                    'message_id' => $messageId,
                    'user_id' => $userId,
                    'emoji' => $emoji,
                ]);
                $added = true;
            }

            return $this->jsonResponse([
                'success' => true,
                'added' => $added,
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to add reaction', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to add reaction'], 500);
        }
    }

    /**
     * Update typing status
     */
    public function updateTypingStatus(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $body = json_decode($request->getPost('body', '{}'), true);
            $chatId = $body['chatId'] ?? null;
            $isTyping = $body['typing'] ?? false;

            if (!$chatId) {
                return $this->jsonResponse(['error' => 'Invalid request'], 400);
            }

            // Verify user is participant
            $chat = Chat::find($chatId);
            if (!$chat || !$chat->hasParticipant($userId)) {
                return $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            if ($isTyping) {
                // Store typing indicator (would typically use Redis or similar)
                DB::table('typing_indicators')->updateOrInsert(
                    ['chat_id' => $chatId, 'user_id' => $userId],
                    [
                        'started_at' => now(),
                        'expires_at' => now()->addSeconds(5),
                    ]
                );
            } else {
                // Remove typing indicator
                DB::table('typing_indicators')
                    ->where('chat_id', $chatId)
                    ->where('user_id', $userId)
                    ->delete();
            }

            return $this->jsonResponse(['success' => true]);

        } catch (\Exception $e) {
            $this->logError('Failed to update typing status', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to update typing status'], 500);
        }
    }

    /**
     * Pin/unpin chat
     */
    public function togglePinChat(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $body = json_decode($request->getPost('body', '{}'), true);
            $chatId = $body['chatId'] ?? null;

            if (!$chatId) {
                return $this->jsonResponse(['error' => 'Invalid request'], 400);
            }

            $participant = ChatParticipant::where('chat_id', $chatId)
                ->where('user_id', $userId)
                ->first();

            if (!$participant) {
                return $this->jsonResponse(['error' => 'Chat not found'], 404);
            }

            $participant->update(['is_pinned' => !$participant->is_pinned]);

            return $this->jsonResponse([
                'success' => true,
                'pinned' => $participant->is_pinned,
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to toggle pin', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to toggle pin'], 500);
        }
    }

    /**
     * Search messages
     */
    public function searchMessages(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->jsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $this->sessionManager->getUser();
        $userId = $user['id'];

        try {
            $query = $request->getQuery('q', '');
            $chatId = $request->getQuery('chatId');
            $limit = min((int)$request->getQuery('limit', 20), 50);

            if (strlen($query) < 2) {
                return $this->jsonResponse(['error' => 'Query too short'], 400);
            }

            $messagesQuery = Message::with(['sender', 'chat'])
                ->whereHas('chat.participants', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->where('body', 'LIKE', '%' . $query . '%');

            if ($chatId) {
                $messagesQuery->where('chat_id', $chatId);
            }

            $messages = $messagesQuery
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $results = $messages->map(function($message) use ($userId) {
                return [
                    'id' => $message->id,
                    'chatId' => $message->chat_id,
                    'chatName' => $this->getChatName($message->chat, $userId),
                    'sender' => $message->sender->full_name,
                    'text' => $this->highlightSearchTerm($message->body, $query),
                    'time' => $message->created_at->format('M j, g:i A'),
                ];
            });

            return $this->jsonResponse([
                'results' => $results,
                'total' => count($results),
            ]);

        } catch (\Exception $e) {
            $this->logError('Failed to search messages', [
                'error' => $e->getMessage(),
                'userId' => $userId,
            ]);

            return $this->jsonResponse(['error' => 'Failed to search messages'], 500);
        }
    }

    /**
     * Helper methods
     */
    private function findExistingDirectChat(string $userId1, string $userId2): ?Chat
    {
        return Chat::where('type', 'direct')
            ->whereHas('participants', function($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('participants', function($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();
    }

    private function formatMessage($message, string $currentUserId): array
    {
        return [
            'id' => $message->id,
            'senderId' => $message->sender_id,
            'senderName' => $message->sender->full_name,
            'senderInitials' => strtoupper(substr($message->sender->first_name, 0, 1) . substr($message->sender->last_name, 0, 1)),
            'senderGradient' => $this->getGradient($message->sender_id),
            'text' => $message->body,
            'time' => $message->created_at->format('g:i A'),
            'type' => $message->type,
            'isOwn' => $message->sender_id === $currentUserId,
            'replyTo' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'text' => substr($message->replyTo->body, 0, 100),
                'sender' => $message->replyTo->sender->first_name,
            ] : null,
            'attachments' => $message->attachments->map(function($attachment) {
                return [
                    'id' => $attachment->id,
                    'type' => $attachment->type,
                    'name' => $attachment->name,
                    'url' => $attachment->url,
                    'size' => $attachment->human_size,
                ];
            })->toArray(),
        ];
    }

    private function getChatName($chat, string $userId): string
    {
        if ($chat->type === 'group') {
            return $chat->name;
        }

        $otherParticipant = $chat->participants
            ->where('user_id', '!=', $userId)
            ->first();

        return $otherParticipant ? $otherParticipant->user->full_name : 'Unknown';
    }

    private function highlightSearchTerm(string $text, string $term): string
    {
        $highlighted = preg_replace(
            '/(' . preg_quote($term, '/') . ')/i',
            '<mark>$1</mark>',
            e($text)
        );
        
        // Return excerpt around the match
        $pos = stripos($text, $term);
        if ($pos !== false) {
            $start = max(0, $pos - 50);
            $length = 150;
            $excerpt = substr($text, $start, $length);
            if ($start > 0) $excerpt = '...' . $excerpt;
            if (strlen($text) > $start + $length) $excerpt .= '...';
            
            return preg_replace(
                '/(' . preg_quote($term, '/') . ')/i',
                '<mark>$1</mark>',
                e($excerpt)
            );
        }
        
        return substr(e($text), 0, 150) . '...';
    }

    private function getGradient(string $id): string
    {
        $gradients = [
            'from-blue-400 to-indigo-600',
            'from-green-400 to-teal-600',
            'from-purple-400 to-pink-600',
            'from-orange-400 to-red-600',
            'from-yellow-400 to-orange-600',
            'from-indigo-400 to-purple-600',
            'from-pink-400 to-rose-600',
            'from-teal-400 to-cyan-600',
        ];
        
        $index = hexdec(substr(md5($id), 0, 2)) % count($gradients);
        return $gradients[$index];
    }

    private function jsonResponse($data, int $status = 200): Response
    {
        $response = new Response();
        $response->setStatusCode($status);
        $response->setHeader('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }
}