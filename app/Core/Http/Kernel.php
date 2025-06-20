<?php



// File: app/Core/Http/Kernel.php
namespace App\Core\Http;

use App\Core\Http\Middleware\MiddlewareInterface;

/**
 * HTTP Kernel - handles request/response cycle with middleware
 */
class Kernel
{
    private array $middleware = [];
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }
    
    public function handle(Request $request): Response
    {
        // Build middleware chain
        $chain = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            function ($request) {
                return $this->dispatch($request);
            }
        );
        
        return $chain($request);
    }
    
    private function dispatch(Request $request): Response
    {
        // Route to controller
        $router = $this->container->get(Router::class);
        return $router->dispatch($request);
    }
}