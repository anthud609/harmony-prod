<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Helpers/csrf.php';

// Show all errors, warnings, notices, deprecated messages, etc.
error_reporting(E_ALL);

// Send errors to the browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use App\Modules\IAM\Controllers\AuthController;
use App\Core\Dashboard\Controllers\DashboardController;
use App\Core\Security\CsrfMiddleware;
use App\Core\Security\CsrfProtection;
use App\Core\Security\SessionManager;
use App\Core\Api\Controllers\SessionController;

// Initialize secure session
try {
    SessionManager::init();
} catch (Exception $e) {
    // Session expired or invalid - redirect to login
    header('Location: /login');
    exit;
}

// Initialize CSRF protection
CsrfProtection::init();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ──────────────────────────────────────────────────────────────────────────
// Define routes that should skip CSRF protection
// ──────────────────────────────────────────────────────────────────────────
$csrfExemptRoutes = [
    '/api/health-check',
    '/webhooks/*', // External webhooks might not have CSRF tokens
];

// ──────────────────────────────────────────────────────────────────────────
// Apply CSRF middleware for non-exempt routes
// ──────────────────────────────────────────────────────────────────────────
$skipCsrf = false;
foreach ($csrfExemptRoutes as $exemptRoute) {
    if (fnmatch($exemptRoute, $requestPath)) {
        $skipCsrf = true;
        break;
    }
}

if (!$skipCsrf && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    $csrfMiddleware = new CsrfMiddleware();
    $csrfMiddleware->handle();
}

// ──────────────────────────────────────────────────────────────────────────
// If already logged in, redirect any "/" or "/login" request to /dashboard
// ──────────────────────────────────────────────────────────────────────────
if (SessionManager::has('user') && in_array($requestPath, ['/', '/login'], true)) {
    header('Location: /dashboard');
    exit;
}

// ──────────────────────────────────────────────────────────────────────────
// Route definitions
// ──────────────────────────────────────────────────────────────────────────
$routes = [
    '/'                    => [AuthController::class,    'showLogin'],
    '/login'               => [AuthController::class,    'showLogin'],
    '/login.post'          => [AuthController::class,    'login'],
    '/logout'              => [AuthController::class,    'logout'],
    '/dashboard'           => [DashboardController::class, 'index'],
    '/user/preferences'    => [AuthController::class,    'updatePreferences'],
    '/notifications/mark-read' => [AuthController::class, 'markNotificationsRead'],
    '/api/session-status'  => [SessionController::class, 'status'],
    '/api/extend-session'  => [SessionController::class, 'extend'],
];

// redirect POST /login to /login.post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath === '/login') {
    $requestPath = '/login.post';
}

// ──────────────────────────────────────────────────────────────────────────
// Auth guard: everything except login routes requires a user
// ──────────────────────────────────────────────────────────────────────────
$publicRoutes = ['/login', '/login.post', '/api/health-check'];
if (!in_array($requestPath, $publicRoutes, true) && !SessionManager::has('user')) {
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
// Dispatch
// ──────────────────────────────────────────────────────────────────────────
if (isset($routes[$requestPath])) {
    [$class, $method] = $routes[$requestPath];
    (new $class)->{$method}();
} else {
    header("HTTP/1.1 404 Not Found");
    echo '404 — Not Found';
}