<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Helpers/csrf.php';

// Show all errors, warnings, notices, deprecated messages, etc.
error_reporting(E_ALL);

// Send errors to the browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use App\Core\Container\ContainerFactory;
use App\Core\Routing\Router;
use App\Modules\IAM\Controllers\AuthController;
use App\Core\Dashboard\Controllers\DashboardController;
use App\Core\Security\CsrfMiddleware;
use App\Core\Security\CsrfProtection;
use App\Core\Security\SessionManager;
use App\Core\Api\Controllers\SessionController;

// Create DI container
$container = ContainerFactory::create();

// Get services from container
$sessionManager = $container->get(SessionManager::class);
$csrfProtection = $container->get(CsrfProtection::class);
$csrfMiddleware = $container->get(CsrfMiddleware::class);

// Initialize secure session
try {
    $sessionManager->init();
} catch (Exception $e) {
    // Session expired or invalid - redirect to login
    header('Location: /login');
    exit;
}

// Initialize CSRF protection
$csrfProtection->init();

// Create and configure router
$router = new Router($container);

// Configure routes
$router->addRoutes([
    '/'                    => [AuthController::class,    'showLogin'],
    '/login'               => [AuthController::class,    'showLogin'],
    '/login.post'          => [AuthController::class,    'login'],
    '/logout'              => [AuthController::class,    'logout'],
    '/dashboard'           => [DashboardController::class, 'index'],
    '/user/preferences'    => [AuthController::class,    'updatePreferences'],
    '/notifications/mark-read' => [AuthController::class, 'markNotificationsRead'],
    '/api/session-status'  => [SessionController::class, 'status'],
    '/api/extend-session'  => [SessionController::class, 'extend'],
]);

// Configure CSRF exempt routes
$router->setCsrfExemptRoutes([
    '/api/health-check',
    '/webhooks/*',
]);

// Configure public routes (no auth required)
$router->setPublicRoutes([
    '/login',
    '/login.post',
    '/api/health-check'
]);

// Get request path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// ──────────────────────────────────────────────────────────────────────────
// Apply CSRF middleware for non-exempt routes
// ──────────────────────────────────────────────────────────────────────────
if (!$router->isCsrfExempt($requestPath) && $requestMethod !== 'GET') {
    $csrfMiddleware->handle();
}

// ──────────────────────────────────────────────────────────────────────────
// If already logged in, redirect any "/" or "/login" request to /dashboard
// ──────────────────────────────────────────────────────────────────────────
if ($sessionManager->has('user') && in_array($requestPath, ['/', '/login'], true)) {
    header('Location: /dashboard');
    exit;
}

// ──────────────────────────────────────────────────────────────────────────
// Auth guard: everything except public routes requires a user
// ──────────────────────────────────────────────────────────────────────────
if (!$router->isPublicRoute($requestPath) && !$sessionManager->has('user')) {
    // For API routes, return 401 instead of redirect
    if (strpos($requestPath, '/api/') === 0) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    header('Location: /login');
    exit;
}

// ──────────────────────────────────────────────────────────────────────────
// Dispatch the request
// ──────────────────────────────────────────────────────────────────────────
$router->dispatch($requestPath, $requestMethod);