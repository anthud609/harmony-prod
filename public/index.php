<?php
// File: public/index.php - FIXED VERSION
// Add this at the very top after <?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer autoloader first
require_once __DIR__ . '/../vendor/autoload.php';

// Load helper functions BEFORE using them
require_once __DIR__ . '/../app/Core/Helpers/config.php';
require_once __DIR__ . '/../app/Core/Helpers/csrf.php';
require_once __DIR__ . '/../app/Core/Helpers/xss.php';
require_once __DIR__ . '/../app/Core/Helpers/logger.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// NOW you can use is_debug() and other helper functions
use App\Core\Container\ContainerFactory;
use App\Core\Http\Request;
use App\Core\Http\Middleware\SessionMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;
use App\Core\Http\Middleware\ErrorHandlerMiddleware;
use App\Core\Http\Middleware\LoggingMiddleware;
use App\Core\Http\Kernel;
use App\Core\Routing\Router;

try {
    // Create DI container
    $container = ContainerFactory::create();
    
    // Create HTTP kernel
    $kernel = new Kernel($container);
    
    // Add middleware stack
    $kernel
        ->addMiddleware($container->get(ErrorHandlerMiddleware::class))
        ->addMiddleware($container->get(LoggingMiddleware::class))
        ->addMiddleware($container->get(SessionMiddleware::class))
        ->addMiddleware($container->get(CsrfMiddleware::class));
    
    // Load routes
    $router = $container->get(Router::class);
    
    // Load web routes
    $webRoutes = require __DIR__ . '/../routes/web.php';
    $router->addRoutes($webRoutes);
    
    // Load API routes
    $apiRoutes = require __DIR__ . '/../routes/api.php';
    $router->addRoutes($apiRoutes);
    
    // Create request and handle it
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    
    // Send response
    $response->send();
    
} catch (Throwable $e) {
    // Emergency error handling
    http_response_code(500);
    
    if (function_exists('is_debug') && is_debug()) {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>500 - Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
    
    // Log the error
    error_log("Fatal error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}