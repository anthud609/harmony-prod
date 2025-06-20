<?php
// File: app/Core/Http/Middleware/ErrorHandlerMiddleware.php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use Psr\Log\LoggerInterface;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private bool $debug;
    
    public function __construct(LoggerInterface $logger, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }
    
    public function handle(Request $request, callable $next): Response
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            $this->logger->error('Request handling error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'uri' => $request->getUri(),
                'method' => $request->getMethod()
            ]);
            
            // For API requests, return JSON error
            if (strpos($request->getUri(), '/api/') === 0) {
                $data = ['error' => 'Internal server error'];
                
                if ($this->debug) {
                    $data['debug'] = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ];
                }
                
                return (new Response())->json($data, 500);
            }
            
            // For regular requests, return HTML error
            $response = new Response();
            $response->setStatusCode(500);
            
            if ($this->debug) {
                $content = $this->renderDebugError($e);
            } else {
                $content = $this->renderProductionError();
            }
            
            $response->setContent($content);
            return $response;
        }
    }
    
    private function renderDebugError(\Exception $e): string
    {
        return sprintf(
            '<!DOCTYPE html>
            <html>
            <head>
                <title>Error - %s</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    h1 { color: #d9534f; }
                    pre { background: #f5f5f5; padding: 15px; overflow-x: auto; }
                    .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <h1>%s</h1>
                <div class="info">
                    <strong>Message:</strong> %s<br>
                    <strong>File:</strong> %s<br>
                    <strong>Line:</strong> %d
                </div>
                <h2>Stack Trace:</h2>
                <pre>%s</pre>
            </body>
            </html>',
            get_class($e),
            get_class($e),
            htmlspecialchars($e->getMessage()),
            $e->getFile(),
            $e->getLine(),
            htmlspecialchars($e->getTraceAsString())
        );
    }
    
    private function renderProductionError(): string
    {
        return '<!DOCTYPE html>
            <html>
            <head>
                <title>500 - Internal Server Error</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                    h1 { color: #333; }
                    p { color: #666; }
                </style>
            </head>
            <body>
                <h1>500 - Internal Server Error</h1>
                <p>Something went wrong. Please try again later.</p>
                <p><a href="/">Go to Homepage</a></p>
            </body>
            </html>';
    }
}