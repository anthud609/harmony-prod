<?php
// File: routes/api.php

use App\Core\Api\Controllers\SessionController;
use App\Core\Api\Controllers\SearchController;
use App\Core\Api\Controllers\NotificationsController;
use App\Core\Api\Controllers\MessagesController;
use App\Core\Api\Controllers\HealthCheckController;
use App\Core\Api\Controllers\ChatController;

return [
    // Health check
    '/api/health' => [HealthCheckController::class, 'check'],
    
    // Session management - ADD THESE!
    '/api/session-status' => [SessionController::class, 'status'],
    '/api/extend-session' => [SessionController::class, 'extend'],
    
    // Search
    '/api/search' => [SearchController::class, 'search'],
    
    // Notifications
    '/api/notifications' => [NotificationsController::class, 'getNotifications'],
    '/api/notifications/mark-viewed' => [NotificationsController::class, 'markAsViewed'],
    '/api/notifications/mark-all-read' => [NotificationsController::class, 'markAllRead'],
    
   // Chat API endpoints
    '/api/messages/send' => [ChatController::class, 'sendMessage'],
    '/api/messages/search' => [ChatController::class, 'searchMessages'],
    '/api/chats/create' => [ChatController::class, 'createChat'],
    '/api/chats/pin' => [ChatController::class, 'togglePinChat'],
    '/api/messages/react' => [ChatController::class, 'addReaction'],
    '/api/chats/typing' => [ChatController::class, 'updateTypingStatus'],
    
    // Get messages and notifications for header dropdowns
    '/api/messages' => [MessagesController::class, 'getMessages'],
    '/api/messages/mark-read' => [MessagesController::class, 'markAsRead'],
    
];