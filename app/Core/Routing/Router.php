<?php

// =============================================================================
// File: app/Core/Api/Controllers/HealthCheckController.php (ALREADY CORRECT)
// =============================================================================
// This controller already returns Response objects properly

// =============================================================================
// File: app/Core/Routing/Router.php (UPDATED)
// =============================================================================

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

        // ALL controllers must now return Response objects
        // Check if method accepts Request parameter
        $reflection = new \ReflectionMethod($controller, $actionMethod);
        $parameters = $reflection->getParameters();

        if (!empty($parameters) && $parameters[0]->getType() && 
            $parameters[0]->getType()->getName() === Request::class) {
            // New controllers that accept Request and return Response
            $response = $controller->{$actionMethod}($request);
        } else {
            // Legacy controllers - call without Request but still expect Response
            $response = $controller->{$actionMethod}();
        }

        // Ensure we got a Response object
        if (!($response instanceof Response)) {
            throw new \RuntimeException(
                sprintf(
                    'Controller %s::%s must return a Response object, got %s',
                    $controllerClass,
                    $actionMethod,
                    is_object($response) ? get_class($response) : gettype($response)
                )
            );
        }

        return $response;
    }

    private function handleNotFound(): Response
    {
        return (new Response())
            ->setStatusCode(404)
            ->setContent('<h1>404 - Not Found</h1><p>The requested page could not be found.</p>');
    }
}