<?php
// File: app/Core/Routing/Router.php
namespace App\Core\Routing;

use DI\Container;

class Router
{
    private Container $container;
    private array $routes = [];
    private array $csrfExemptRoutes = [];
    private array $publicRoutes = [];
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Register a route
     */
    public function add(string $path, string $controller, string $method): self
    {
        $this->routes[$path] = [$controller, $method];
        return $this;
    }
    
    /**
     * Add multiple routes at once
     */
    public function addRoutes(array $routes): self
    {
        foreach ($routes as $path => $handler) {
            $this->add($path, $handler[0], $handler[1]);
        }
        return $this;
    }
    
    /**
     * Set CSRF exempt routes
     */
    public function setCsrfExemptRoutes(array $routes): self
    {
        $this->csrfExemptRoutes = $routes;
        return $this;
    }
    
    /**
     * Set public routes (no auth required)
     */
    public function setPublicRoutes(array $routes): self
    {
        $this->publicRoutes = $routes;
        return $this;
    }
    
    /**
     * Check if route is CSRF exempt
     */
    public function isCsrfExempt(string $path): bool
    {
        foreach ($this->csrfExemptRoutes as $exemptRoute) {
            if (fnmatch($exemptRoute, $path)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if route is public
     */
    public function isPublicRoute(string $path): bool
    {
        return in_array($path, $this->publicRoutes, true);
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch(string $path, string $method = 'GET'): void
    {
        // Handle special routing logic
        if ($method === 'POST' && $path === '/login') {
            $path = '/login.post';
        }
        
        if (!isset($this->routes[$path])) {
            $this->handleNotFound();
            return;
        }
        
        [$controllerClass, $controllerMethod] = $this->routes[$path];
        
        // Get controller instance from container
        $controller = $this->container->get($controllerClass);
        
        // Call the method
        $controller->{$controllerMethod}();
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        header("HTTP/1.1 404 Not Found");
        echo '404 — Not Found';
    }
}