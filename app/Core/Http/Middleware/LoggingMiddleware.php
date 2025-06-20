<?php
// File: app/Core/Http/Middleware/LoggingMiddleware.php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use Psr\Log\LoggerInterface;

class LoggingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        
        // Log request
        $this->logger->info('Request started', [
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Process request
        $response = $next($request);
        
        // Calculate execution time
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log response
        $this->logger->info('Request completed', [
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ]);
        
        return $response;
    }
}