<?php
// File: routes/api.php

return [
    // Session Management
    '/session-status' => ['App\Core\Api\Controllers\SessionController', 'status'],
    '/extend-session' => ['App\Core\Api\Controllers\SessionController', 'extend'],
    
    // Search
    '/search' => ['App\Core\Api\Controllers\SearchController', 'search'],
    
    // Messages
    '/messages' => ['App\Core\Api\Controllers\MessagesController', 'getMessages'],
    '/messages/mark-read' => ['App\Core\Api\Controllers\MessagesController', 'markAsRead'],
    
    // Notifications
    '/notifications' => ['App\Core\Api\Controllers\NotificationsController', 'getNotifications'],
    '/notifications/mark-viewed' => ['App\Core\Api\Controllers\NotificationsController', 'markAsViewed'],
    '/notifications/mark-all-read' => ['App\Core\Api\Controllers\NotificationsController', 'markAllRead'],
    
    // Health Check
    '/health-check' => ['App\Core\Api\Controllers\HealthCheckController', 'check'],
];