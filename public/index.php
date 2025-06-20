<?php
// File: public/index.php (Updated Application class with configuration)

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Config\ConfigManager;
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
        $this->loadConfiguration();
        $this->configureErrorReporting();
        $this->configureTimezone();
        $this->createContainer();
        $this->setupLogger();
        $this->checkMaintenanceMode();
    }
    
    private function loadConfiguration(): void
    {
        // Initialize configuration manager (which loads .env)
        $this->config = ConfigManager::getInstance();
        
        // Log environment information
        if (!is_production()) {
            error_log(sprintf(
                '[Harmony] Starting application in %s mode (debug: %s)',
                app_env(),
                is_debug() ? 'enabled' : 'disabled'
            ));
        }
    }
    
    private function configureErrorReporting(): void
    {
        if (is_debug()) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }
    }
    
    private function configureTimezone(): void
    {
        date_default_timezone_set(config('app.timezone', 'UTC'));
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
        
        // Log application start
        $this->logger->info('Application starting', [
            'environment' => app_env(),
            'debug' => is_debug(),
            'timezone' => config('app.timezone'),
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ]);
    }
    
    private function checkMaintenanceMode(): void
    {
        if (!config('app.maintenance.enabled', false)) {
            return;
        }
        
        // Check if IP is allowed
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        $allowedIps = config('app.maintenance.allowed_ips', []);
        
        if (in_array($clientIp, $allowedIps)) {
            return;
        }
        
        // Return maintenance response
        http_response_code(503);
        header('Retry-After: ' . config('app.maintenance.retry_after', 3600));
        
        $message = config('app.maintenance.message', 'We are currently performing scheduled maintenance.');
        
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance - {$this->config->get('app.name')}</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
        p { color: #666; }
    </style>
</head>
<body>
    <h1>Maintenance Mode</h1>
    <p>{$message}</p>
    <p>Please try again later.</p>
</body>
</html>
HTML;
        exit;
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
            'line' => $line,
            'environment' => app_env()
        ]);
        
        // In debug mode, let PHP handle the error normally
        if (is_debug()) {
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
            'trace' => $exception->getTraceAsString(),
            'environment' => app_env()
        ]);
        
        // Create error response
        $response = new Response();
        $response->setStatusCode(500);
        
        if (is_debug()) {
            $content = $this->renderDebugException($exception);
        } else {
            $content = $this->renderProductionError();
        }
        
        $response->setContent($content);
        $response->send();
        exit;
    }
    
    private function renderDebugException($exception): string
    {
        $appName = config('app.name');
        return sprintf(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>Error - %s</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                    .container { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
                    h1 { color: #d9534f; margin-top: 0; }
                    .exception { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    pre { background: #f5f5f5; padding: 15px; overflow-x: auto; border-radius: 5px; }
                    .info { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
                    .label { font-weight: bold; color: #666; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>%s - %s</h1>
                    <div class="exception">
                        <div class="info">
                            <span class="label">Message:</span> %s
                        </div>
                        <div class="info">
                            <span class="label">File:</span> %s
                        </div>
                        <div class="info">
                            <span class="label">Line:</span> %d
                        </div>
                        <div class="info">
                            <span class="label">Environment:</span> %s
                        </div>
                    </div>
                    <h2>Stack Trace:</h2>
                    <pre>%s</pre>
                </div>
            </body>
            </html>',
            $appName,
            get_class($exception),
            $appName,
            htmlspecialchars($exception->getMessage()),
            $exception->getFile(),
            $exception->getLine(),
            app_env(),
            htmlspecialchars($exception->getTraceAsString())
        );
    }
    
    private function renderProductionError(): string
    {
        $appName = config('app.name');
        return sprintf(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>500 - Internal Server Error</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; text-align: center; background: #f5f5f5; }
                    .container { background: white; padding: 50px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; }
                    h1 { color: #333; margin-top: 0; }
                    p { color: #666; }
                    a { color: #337ab7; text-decoration: none; }
                    a:hover { text-decoration: underline; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>500 - Internal Server Error</h1>
                    <p>Something went wrong. Please try again later.</p>
                    <p><a href="/">Go to Homepage</a></p>
                </div>
            </body>
            </html>'
        );
    }
    
    public function createKernel(): Kernel
    {
        $kernel = new Kernel($this->container);
        
        // Configure middleware stack (order matters - first added is outermost)
        $kernel->addMiddleware(
            new ErrorHandlerMiddleware($this->logger, is_debug())
        );
        
        // Only add logging middleware if enabled
        if (config('logging.middleware.enabled', true)) {
            $kernel->addMiddleware(
                new LoggingMiddleware($this->logger)
            );
        }
        
        $kernel->addMiddleware(
            new SessionMiddleware(
                $this->container->get(SessionManager::class),
                config('session.no_update_routes', ['/api/session-status'])
            )
        );
        
        $kernel->addMiddleware(
            new CsrfMiddleware(
                $this->container->get(CsrfProtection::class),
                config('security.csrf.exempt_routes', [
                    '/api/health-check',
                    '/api/session-status',
                    '/webhooks/*'
                ])
            )
        );
        
        $kernel->addMiddleware(
            new AuthenticationMiddleware(
                $this->container->get(SessionManager::class),
                config('auth.public_routes', [
                    '/',
                    '/login',
                    '/login.post',
                    '/api/health-check'
                ])
            )
        );
        
        return $kernel;
    }
    
    public function configureRoutes(): void
    {
        $router = $this->container->get(Router::class);
        
        // Load routes from configuration or define them here
        $routes = require base_path('routes/web.php');
        $router->addRoutes($routes);
        
        // API routes
        if (feature('api_access')) {
            $apiRoutes = require base_path('routes/api.php');
            foreach ($apiRoutes as $path => $handler) {
                $router->add('/api' . $path, $handler);
            }
        }
    }
    
    public function run(): void
    {
        $this->logger->info('Application started', [
            'name' => config('app.name'),
            'version' => config('app.version'),
            'environment' => app_env(),
            'debug' => is_debug() ? 'enabled' : 'disabled',
            'features' => array_keys(array_filter(config('features', [])))
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
        
        // Log request completion
        if (config('logging.requests.enabled', true)) {
            $this->logger->info('Request completed', [
                'status' => $response->getStatusCode(),
                'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
            ]);
        }
    }
}

// Bootstrap and run the application
try {
    $app = new Application();
    $app->run();
} catch (\Exception $e) {
    // Fatal error during bootstrap
    error_log('[Harmony] Fatal bootstrap error: ' . $e->getMessage());
    http_response_code(500);
    echo is_debug() 
        ? '<h1>Bootstrap Error</h1><pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>'
        : '<h1>500 - Internal Server Error</h1><p>The application could not be started.</p>';
    exit(1);
}