<?php
// File: public/index.php
// Entry point for the application

// Simple Application class to bootstrap the app
class Application
{
    private $container;
    
    public function __construct()
    {
        $this->bootstrapAutoloader();
        $this->loadHelpers(); // Load helpers BEFORE configuration
        $this->loadConfiguration();
        $this->bootstrapContainer();
    }
    
    private function bootstrapAutoloader(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    
    private function loadHelpers(): void
    {
        // Load helper files that define global functions
        // These need to be loaded before configuration
        $helpers = [
            __DIR__ . '/../app/Core/Helpers/config.php',
            __DIR__ . '/../app/Core/Helpers/csrf.php',
            __DIR__ . '/../app/Core/Helpers/xss.php',
            __DIR__ . '/../app/Core/Helpers/logger.php',
        ];
        
        foreach ($helpers as $helper) {
            if (file_exists($helper)) {
                require_once $helper;
            }
        }
    }
    
    private function loadConfiguration(): void
    {
        // Configuration can now safely use env() and other helper functions
        \App\Core\Config\ConfigManager::getInstance();
    }
    
    private function bootstrapContainer(): void
    {
        $this->container = \App\Core\Container\ContainerFactory::create();
    }
    
    public function run(): void
    {
        try {
            $this->handleRequest();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function handleRequest(): void
    {
        // Start session
        $sessionManager = $this->container->get(\App\Core\Security\SessionManager::class);
        $sessionManager->init();
        
        // Initialize CSRF protection
        $csrfProtection = $this->container->get(\App\Core\Security\CsrfProtection::class);
        $csrfProtection->init();
        
        // Get router and routes
        $router = $this->container->get(\App\Core\Routing\Router::class);
        
        // Define routes
        $routes = [
            '/' => [\App\Core\Dashboard\Controllers\DashboardController::class, 'index'],
            '/dashboard' => [\App\Core\Dashboard\Controllers\DashboardController::class, 'index'],
            '/login' => [\App\Modules\IAM\Controllers\AuthController::class, 'showLogin'],
            '/login.post' => [\App\Modules\IAM\Controllers\AuthController::class, 'login'],
            '/logout' => [\App\Modules\IAM\Controllers\AuthController::class, 'logout'],
            
            // API routes
            '/api/session-status' => [\App\Core\Api\Controllers\SessionController::class, 'status'],
            '/api/extend-session' => [\App\Core\Api\Controllers\SessionController::class, 'extend'],
            '/api/search' => [\App\Core\Api\Controllers\SearchController::class, 'search'],
            '/api/messages' => [\App\Core\Api\Controllers\MessagesController::class, 'getMessages'],
            '/api/messages/mark-read' => [\App\Core\Api\Controllers\MessagesController::class, 'markAsRead'],
            '/api/notifications' => [\App\Core\Api\Controllers\NotificationsController::class, 'getNotifications'],
            '/api/notifications/mark-viewed' => [\App\Core\Api\Controllers\NotificationsController::class, 'markAsViewed'],
            '/api/notifications/mark-all-read' => [\App\Core\Api\Controllers\NotificationsController::class, 'markAllRead'],
            
            // User preferences
            '/user/preferences' => [\App\Modules\IAM\Controllers\AuthController::class, 'updatePreferences'],
            '/notifications/mark-read' => [\App\Modules\IAM\Controllers\AuthController::class, 'markNotificationsRead'],
        ];
        
        $router->addRoutes($routes);
        
        // Create HTTP kernel with middleware
        $kernel = new \App\Core\Http\Kernel($this->container);
        
        // Add middleware in order
        $kernel->addMiddleware(new \App\Core\Http\Middleware\ErrorHandlerMiddleware(
            $this->container->get(\Psr\Log\LoggerInterface::class),
            config('app.debug', false)
        ));
        
        $kernel->addMiddleware(new \App\Core\Http\Middleware\LoggingMiddleware(
            $this->container->get(\Psr\Log\LoggerInterface::class)
        ));
        
        $kernel->addMiddleware(new \App\Core\Http\Middleware\SessionMiddleware(
            $this->container->get(\App\Core\Security\SessionManager::class),
            ['/api/session-status'] // Don't update activity for these routes
        ));
        
        $kernel->addMiddleware(new \App\Core\Http\Middleware\CsrfMiddleware(
            $this->container->get(\App\Core\Security\CsrfProtection::class),
            ['/api/health', '/api/session-status'] // Exempt routes
        ));
        
        $kernel->addMiddleware(new \App\Core\Http\Middleware\AuthenticationMiddleware(
            $this->container->get(\App\Core\Security\SessionManager::class),
            ['/login', '/login.post', '/api/health'] // Public routes
        ));
        
        // Create request from globals
        $request = \App\Core\Http\Request::createFromGlobals();
        
        // Handle the request
        $response = $kernel->handle($request);
        
        // Send the response
        $response->send();
    }
    
    private function handleException(\Exception $e): void
    {
        // Basic error handling for bootstrap errors
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        
        if (config('app.debug', false)) {
            echo '<pre>';
            echo 'Error: ' . htmlspecialchars($e->getMessage()) . "\n";
            echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n";
            echo "\nStack trace:\n";
            echo htmlspecialchars($e->getTraceAsString());
            echo '</pre>';
        } else {
            http_response_code(500);
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - Harmony HRMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
        h1 { color: #333; }
        p { color: #666; margin: 20px 0; }
        a { color: #4F46E5; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Oops! Something went wrong</h1>
    <p>We\'re sorry, but something went wrong. Please try again later.</p>
    <p><a href="/">Go to Homepage</a></p>
</body>
</html>';
        }
        exit(1);
    }
}

// Bootstrap and run the application
try {
    $app = new Application();
    $app->run();
} catch (\Exception $e) {
    // Last resort error handler
    error_log('Fatal bootstrap error: ' . $e->getMessage());
    http_response_code(500);
    echo '<h1>System Error</h1><p>The application could not be started. Please contact the system administrator.</p>';
    if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    }
    exit(1);
}