<?php

// File: app/Core/Http/Middleware/AuthenticationMiddleware.php
namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private SessionManager $sessionManager;
    private array $publicRoutes;
    
    public function __construct(SessionManager $sessionManager, array $publicRoutes = [])
    {
        $this->sessionManager = $sessionManager;
        $this->publicRoutes = $publicRoutes;
    }
    
    public function handle(Request $request, callable $next): Response
    {
        $uri = $request->getUri();
        
        // Check if route is public
        foreach ($this->publicRoutes as $route) {
            if (fnmatch($route, $uri)) {
                return $next($request);
            }
        }
        
        // Check authentication
        if (!$this->sessionManager->isLoggedIn()) {
            // For API routes, return JSON error
            if (strpos($uri, '/api/') === 0) {
                return (new Response())
                    ->json(['error' => 'Authentication required'], 401);
            }
            
            // For regular routes, redirect to login
            return (new Response())->redirect('/login');
        }
        
        // Add user to request attributes
        $request->setAttribute('user', $this->sessionManager->getUser());
        
        return $next($request);
    }
}