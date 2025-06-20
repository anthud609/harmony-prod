<?php

// File: app/Core/Http/Middleware/CsrfMiddleware.php (Updated)

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\CsrfException;
use App\Core\Security\CsrfProtection;

class CsrfMiddleware implements MiddlewareInterface
{
    private CsrfProtection $csrfProtection;
    private array $exemptRoutes;

    public function __construct(CsrfProtection $csrfProtection, array $exemptRoutes = [])
    {
        $this->csrfProtection = $csrfProtection;
        $this->exemptRoutes = $exemptRoutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        // Initialize CSRF protection
        $this->csrfProtection->init();

        // Skip CSRF check for GET requests and exempt routes
        if ($request->getMethod() === 'GET' || $this->isExempt($request->getUri())) {
            return $next($request);
        }

        try {
            $this->csrfProtection->verifyRequest();
        } catch (CsrfException $e) {
            // For AJAX requests, return JSON error
            if ($this->isAjaxRequest()) {
                return (new Response())->json([
                    'error' => 'CSRF token validation failed',
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 403);
            }

            // For regular requests, redirect back with error
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            $_SESSION['csrf_error'] = 'Your session has expired. Please refresh the page and try again.';

            return (new Response())->redirect($referer, 303);
        }

        return $next($request);
    }

    private function isExempt(string $uri): bool
    {
        foreach ($this->exemptRoutes as $route) {
            if (fnmatch($route, $uri)) {
                return true;
            }
        }

        return false;
    }

    private function isAjaxRequest(): bool
    {
        return ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
