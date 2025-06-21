<?php
// ==============================================================================
// STEP 1: Update the API routes (routes/api.php)
// ==============================================================================

use App\Core\Api\Controllers\MessagesController;
use App\Core\Api\Controllers\NotificationsController;
use App\Core\Api\Controllers\SearchController;
use App\Core\Api\Controllers\SessionController;
use App\Core\Api\Controllers\HealthCheckController;

return [
    // Health check
    '/api/health' => [HealthCheckController::class, 'check'],
    
    // Session management
    '/api/session-status' => [SessionController::class, 'status'],
    '/api/extend-session' => [SessionController::class, 'extend'],
    
    // Messages
    '/api/messages' => [MessagesController::class, 'getMessages'],
    '/api/messages/mark-read' => [MessagesController::class, 'markAsRead'],
    
    // Notifications  
    '/api/notifications' => [NotificationsController::class, 'getNotifications'],
    '/api/notifications/mark-viewed' => [NotificationsController::class, 'markAsViewed'],
    '/api/notifications/mark-all-read' => [NotificationsController::class, 'markAllRead'],
    
    // Search
    '/api/search' => [SearchController::class, 'search'],
];
