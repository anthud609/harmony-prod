<?php
// File: routes/web.php
use App\Core\Dashboard\Controllers\DashboardController;
use App\Modules\IAM\Controllers\AuthController;

// The $router variable is available from index.php

// Auth routes
$router->add('/login', [AuthController::class, 'showLogin']);
$router->add('/login.post', [AuthController::class, 'login']); // POST to /login handled by router
$router->add('/logout', [AuthController::class, 'logout']);

// Protected routes
$router->add('/', [DashboardController::class, 'index']);
$router->add('/dashboard', [DashboardController::class, 'index']);

// User preferences
$router->add('/user/preferences', [AuthController::class, 'updatePreferences']);
$router->add('/notifications/mark-read', [AuthController::class, 'markNotificationsRead']);