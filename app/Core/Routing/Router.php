<?php
// File: app/Core/Routing/Router.php (Updated to work with Request/Response)
namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use DI\Container;

class Router
{
    private Container $container;
    private array $routes = [];
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function add(string $path, array $handler): self
    {
        $this->routes[$path] = $handler;
        return $this;
    }
    
    public function addRoutes(array $routes): self
    {
        foreach ($routes as $path => $handler) {
            $this->add($path, $handler);
        }
        return $this;
    }
    
    public function dispatch(Request $request): Response
    {
        $path = $request->getUri();
        $method = $request->getMethod();
        
        // Handle special routing logic
        if ($method === 'POST' && $path === '/login') {
            $path = '/login.post';
        }
        
        if (!isset($this->routes[$path])) {
            return $this->handleNotFound();
        }
        
        [$controllerClass, $actionMethod] = $this->routes[$path];
        
        // Get controller instance from container
        $controller = $this->container->get($controllerClass);
        
        // Check if controller method expects Request/Response pattern
        $reflection = new \ReflectionMethod($controller, $actionMethod);
        $parameters = $reflection->getParameters();
        
        if (!empty($parameters) && $parameters[0]->getType() && 
            $parameters[0]->getType()->getName() === Request::class) {
            // New clean controllers that accept Request and return Response
            return $controller->{$actionMethod}($request);
        } else {
            // Legacy controllers - wrap their output
            ob_start();
            $controller->{$actionMethod}();
            $content = ob_get_clean();
            
            $response = new Response();
            $response->setContent($content);
            return $response;
        }
    }
    
    private function handleNotFound(): Response
    {
        return (new Response())
            ->setStatusCode(404)
            ->setContent('<h1>404 - Not Found</h1><p>The requested page could not be found.</p>');
    }
}