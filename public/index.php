<?php
// File: public/index.php (FIXED VERSION)
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container\ContainerFactory;
use App\Core\Routing\Router;
use App\Core\Http\Request;
use App\Core\Http\Kernel;
use App\Core\Http\Middleware\ErrorHandlerMiddleware;
use App\Core\Http\Middleware\SessionMiddleware;
use App\Core\Http\Middleware\LoggingMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;
use App\Core\Http\Middleware\AuthenticationMiddleware;
use App\Core\Security\SessionManager;
use App\Core\Security\CsrfProtection;
use Psr\Log\LoggerInterface;

try {
    // Create DI container
    $container = ContainerFactory::create();
    
    // Create HTTP kernel
    $kernel = new Kernel($container);
    
    // Get logger
    $logger = $container->get(LoggerInterface::class);
    
    // Add middleware in correct order
    // 1. Error handling (outermost)
    $kernel->addMiddleware(new ErrorHandlerMiddleware(
        $logger,
        $_ENV['APP_DEBUG'] ?? false
    ));
    
    // 2. Logging
    $kernel->addMiddleware(new LoggingMiddleware($logger));
    
    // 3. Session handling - with specific routes that don't update activity
    $kernel->addMiddleware(new SessionMiddleware(
        $container->get(SessionManager::class),
        ['/api/session-status', '/api/extend-session']
    ));
    
    // 4. CSRF protection
    $kernel->addMiddleware(new CsrfMiddleware(
        $container->get(CsrfProtection::class),
        ['/api/*', '/health'] // Exempt API routes if needed
    ));
    
    // 5. Authentication - with public routes
    $kernel->addMiddleware(new AuthenticationMiddleware(
        $container->get(SessionManager::class),
        ['/login', '/login.post', '/health', '/api/health']
    ));
    
    // Load routes
    $router = $container->get(Router::class);
    require_once __DIR__ . '/../routes/web.php';
    require_once __DIR__ . '/../routes/api.php';
    
    // Create request from globals
    $request = Request::createFromGlobals();
    
    // Handle the request through middleware chain
    $response = $kernel->handle($request);
    
    // Send the response
    $response->send();
    
    // Ensure script ends after sending response
    exit;
    
} catch (\Exception $e) {
    // Emergency error handling
    error_log('Fatal error in index.php: ' . $e->getMessage());
    
    http_response_code(500);
    
    if (($_ENV['APP_DEBUG'] ?? false) === true || ($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}