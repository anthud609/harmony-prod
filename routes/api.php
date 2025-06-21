<?php
// File: routes/api.php

use App\Core\Api\Controllers\SessionController;
use App\Core\Api\Controllers\SearchController;
use App\Core\Api\Controllers\NotificationsController;
use App\Core\Api\Controllers\MessagesController;
use App\Core\Api\Controllers\HealthCheckController;

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
    
    // Messages
    '/api/messages' => [MessagesController::class, 'getMessages'],
    '/api/messages/mark-read' => [MessagesController::class, 'markAsRead'],
];