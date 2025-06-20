<?php
// File: routes/web.php

return [
    // Authentication Routes
    '/' => ['App\Modules\IAM\Controllers\AuthController', 'showLogin'],
    '/login' => ['App\Modules\IAM\Controllers\AuthController', 'showLogin'],
    '/login.post' => ['App\Modules\IAM\Controllers\AuthController', 'login'],
    '/logout' => ['App\Modules\IAM\Controllers\AuthController', 'logout'],
    
    // Dashboard
    '/dashboard' => ['App\Core\Dashboard\Controllers\DashboardController', 'index'],
    '/dashboard/widget' => ['App\Core\Dashboard\Controllers\DashboardController', 'updateWidget'],
    
    // User Preferences
    '/user/preferences' => ['App\Modules\IAM\Controllers\AuthController', 'updatePreferences'],
    '/notifications/mark-read' => ['App\Modules\IAM\Controllers\AuthController', 'markNotificationsRead'],
];