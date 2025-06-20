<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Helpers/csrf.php';

use App\Core\Container\ContainerFactory;
use App\Core\Routing\Router;
use App\Modules\IAM\Controllers\AuthController;
use App\Core\Dashboard\Controllers\DashboardController;
use App\Core\Security\CsrfMiddleware;
use App\Core\Security\CsrfProtection;
use App\Core\Security\SessionManager;
use App\Core\Api\Controllers\SessionController;
use Psr\Log\LoggerInterface;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Get environment settings
$appEnv = $_ENV['APP_ENV'] ?? 'production';
$appDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

// Configure error reporting based on environment
if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Create DI container
$container = ContainerFactory::create();

// Get logger
$logger = $container->get(LoggerInterface::class);

// Set up error handler to log PHP errors
set_error_handler(function ($severity, $message, $file, $line) use ($logger) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $logger->error('PHP Error', [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ]);
    
    // In debug mode, let PHP handle the error normally
    if ($_ENV['APP_DEBUG'] ?? false) {
        return false;
    }
    
    // In production, suppress the error
    return true;
});

// Set up exception handler
set_exception_handler(function ($exception) use ($logger) {
    $logger->critical('Uncaught Exception', [
        'exception' => get_class($exception),
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    // Show error page
    http_response_code(500);
    
    if ($_ENV['APP_DEBUG'] ?? false) {
        // In debug mode, show the exception
        echo '<h1>Error</h1>';
        echo '<p><strong>' . get_class($exception) . ':</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p>in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    } else {
        // In production, show generic error
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
    
    exit;
});

// Log application start
$logger->info('Application started', [
    'environment' => $appEnv,
    'debug' => $appDebug ? 'enabled' : 'disabled'
]);

// Get services from container
$sessionManager = $container->get(SessionManager::class);
$csrfProtection = $container->get(CsrfProtection::class);
$csrfMiddleware = $container->get(CsrfMiddleware::class);

// Get request path first
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Initialize secure session
// Don't update activity timestamp for session status checks
$updateActivity = (strpos($requestPath, '/api/session-status') === false);

try {
    $sessionManager->init($updateActivity);
    
    $logger->debug('Session initialized', [
        'path' => $requestPath,
        'updateActivity' => $updateActivity,
        'remainingLifetime' => $sessionManager->getRemainingLifetime()
    ]);
    
} catch (Exception $e) {
    $logger->warning('Session initialization failed', [
        'error' => $e->getMessage(),
        'path' => $requestPath
    ]);
    
    // For API calls, return JSON error
    if (strpos($requestPath, '/api/') === 0) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Session expired', 'message' => $e->getMessage()]);
        exit;
    }
    
    // For regular requests, redirect to login
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
    '/api/session-status', // Exempt session status check from CSRF
    '/webhooks/*',
]);

// Configure public routes (no auth required)
$router->setPublicRoutes([
    '/',
    '/login',
    '/login.post',
    '/api/health-check'
]);

// Apply CSRF middleware for non-exempt routes
if (!$router->isCsrfExempt($requestPath) && $requestMethod !== 'GET') {
    try {
        $csrfMiddleware->handle();
    } catch (Exception $e) {
        $logger->warning('CSRF verification failed', [
            'path' => $requestPath,
            'method' => $requestMethod
        ]);
        throw $e;
    }
}

// If already logged in, redirect any "/" or "/login" request to /dashboard
if ($sessionManager->has('user') && in_array($requestPath, ['/', '/login'], true)) {
    $logger->debug('Redirecting logged-in user to dashboard', [
        'username' => $sessionManager->get('user')['username'] ?? 'unknown'
    ]);
    header('Location: /dashboard');
    exit;
}

// Auth guard: everything except public routes requires a user
if (!$router->isPublicRoute($requestPath) && !$sessionManager->has('user')) {
    $logger->info('Access denied - authentication required', [
        'path' => $requestPath
    ]);
    
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

// Log the request
$logger->info('Request', [
    'path' => $requestPath,
    'method' => $requestMethod,
    'user' => $sessionManager->has('user') ? $sessionManager->get('user')['username'] : 'guest'
]);

// Dispatch the request
try {
    $router->dispatch($requestPath, $requestMethod);
} catch (Exception $e) {
    $logger->error('Request dispatch failed', [
        'path' => $requestPath,
        'method' => $requestMethod,
        'error' => $e->getMessage()
    ]);
    throw $e;
}