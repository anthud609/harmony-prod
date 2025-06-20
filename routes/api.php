<?php

// File: routes/api.php
use App\Core\Api\Controllers\HealthCheckController;
use App\Core\Api\Controllers\MessagesController;
use App\Core\Api\Controllers\NotificationsController;
use App\Core\Api\Controllers\SearchController;
use App\Core\Api\Controllers\SessionController;

// The $router variable is available from index.php

// Health check
$router->add('/health', [HealthCheckController::class, 'check']);
$router->add('/api/health', [HealthCheckController::class, 'check']);

// Session management
$router->add('/api/session-status', [SessionController::class, 'status']);
$router->add('/api/extend-session', [SessionController::class, 'extend']);

// Search
$router->add('/api/search', [SearchController::class, 'search']);

// Messages
$router->add('/api/messages', [MessagesController::class, 'getMessages']);
$router->add('/api/messages/mark-read', [MessagesController::class, 'markAsRead']);

// Notifications
$router->add('/api/notifications', [NotificationsController::class, 'getNotifications']);
$router->add('/api/notifications/mark-viewed', [NotificationsController::class, 'markAsViewed']);
$router->add('/api/notifications/mark-all-read', [NotificationsController::class, 'markAllRead']);
