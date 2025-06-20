<?php
// File: public/index.php (Clean Architecture Version)

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container\ContainerFactory;
use App\Core\Http\Kernel;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Http\Middleware\AuthenticationMiddleware;
use App\Core\Http\Middleware\CsrfMiddleware;
use App\Core\Http\Middleware\SessionMiddleware;
use App\Core\Http\Middleware\ErrorHandlerMiddleware;
use App\Core\Http\Middleware\LoggingMiddleware;
use App\Core\Routing\Router;
use App\Core\Security\SessionManager;
use App\Core\Security\CsrfProtection;
use Psr\Log\LoggerInterface;

// Bootstrap the application
class Application
{
    private $container;
    private $logger;
    private $config;
    
    public function __construct()
    {
        $this->loadEnvironment();
        $this->configureErrorReporting();
        $this->createContainer();
        $this->setupLogger();
    }
    
    private function loadEnvironment(): void
    {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
        
        $this->config = [
            'env' => $_ENV['APP_ENV'] ?? 'production',
            'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }
    
    private function configureErrorReporting(): void
    {
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }
    }
    
    private function createContainer(): void
    {
        $this->container = ContainerFactory::create();
    }
    
    private function setupLogger(): void
    {
        $this->logger = $this->container->get(LoggerInterface::class);
        
        // Set up global error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    public function handleError($severity, $message, $file, $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $this->logger->error('PHP Error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ]);
        
        // In debug mode, let PHP handle the error normally
        if ($this->config['debug']) {
            return false;
        }
        
        // In production, suppress the error
        return true;
    }
    
    public function handleException($exception): void
    {
        $this->logger->critical('Uncaught Exception', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Create error response
        $response = new Response();
        $response->setStatusCode(500);
        
        if ($this->config['debug']) {
            $content = sprintf(
                '<h1>Error</h1><p><strong>%s:</strong> %s</p><p>in %s on line %d</p><pre>%s</pre>',
                get_class($exception),
                htmlspecialchars($exception->getMessage()),
                $exception->getFile(),
                $exception->getLine(),
                htmlspecialchars($exception->getTraceAsString())
            );
        } else {
            $content = '<h1>500 - Internal Server Error</h1><p>Something went wrong. Please try again later.</p>';
        }
        
        $response->setContent($content);
        $response->send();
        exit;
    }
    
    public function createKernel(): Kernel
    {
        $kernel = new Kernel($this->container);
        
        // Configure middleware stack (order matters - first added is outermost)
        $kernel->addMiddleware(
            new ErrorHandlerMiddleware($this->logger, $this->config['debug'])
        );
        
        $kernel->addMiddleware(
            new LoggingMiddleware($this->logger)
        );
        
        $kernel->addMiddleware(
            new SessionMiddleware(
                $this->container->get(SessionManager::class),
                ['/api/session-status'] // Don't update activity for these routes
            )
        );
        
        $kernel->addMiddleware(
            new CsrfMiddleware(
                $this->container->get(CsrfProtection::class),
                [
                    '/api/health-check',
                    '/api/session-status',
                    '/webhooks/*'
                ]
            )
        );
        
        $kernel->addMiddleware(
            new AuthenticationMiddleware(
                $this->container->get(SessionManager::class),
                [
                    '/',
                    '/login',
                    '/login.post',
                    '/api/health-check'
                ]
            )
        );
        
        return $kernel;
    }
    
    public function configureRoutes(): void
    {
        $router = $this->container->get(Router::class);
        
        // Configure routes using the new clean controllers
        $router->addRoutes([
            '/' => ['App\Modules\IAM\Controllers\AuthController', 'showLogin'],
            '/login' => ['App\Modules\IAM\Controllers\AuthController', 'showLogin'],
            '/login.post' => ['App\Modules\IAM\Controllers\AuthController', 'login'],
            '/logout' => ['App\Modules\IAM\Controllers\AuthController', 'logout'],
            '/dashboard' => ['App\Core\Dashboard\Controllers\DashboardController', 'index'],
            '/dashboard/widget' => ['App\Core\Dashboard\Controllers\DashboardController', 'updateWidget'],
            '/user/preferences' => ['App\Modules\IAM\Controllers\AuthController', 'updatePreferences'],
            '/notifications/mark-read' => ['App\Modules\IAM\Controllers\AuthController', 'markNotificationsRead'],
            '/api/session-status' => ['App\Core\Api\Controllers\SessionController', 'status'],
            '/api/extend-session' => ['App\Core\Api\Controllers\SessionController', 'extend'],
        ]);
    }
    
    public function run(): void
    {
        $this->logger->info('Application started', [
            'environment' => $this->config['env'],
            'debug' => $this->config['debug'] ? 'enabled' : 'disabled'
        ]);
        
        // Configure routes
        $this->configureRoutes();
        
        // Create HTTP kernel with middleware
        $kernel = $this->createKernel();
        
        // Create request from PHP globals
        $request = Request::createFromGlobals();
        
        // Handle the request
        try {
            $response = $kernel->handle($request);
        } catch (\Exception $e) {
            // This should rarely happen as ErrorHandlerMiddleware should catch most errors
            $this->handleException($e);
            return;
        }
        
        // Send the response
        $response->send();
    }
}

// Bootstrap and run the application
$app = new Application();
$app->run();