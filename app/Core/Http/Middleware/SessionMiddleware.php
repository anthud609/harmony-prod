<?php

// File: app/Core/Http/Middleware/SessionMiddleware.php

namespace App\Core\Http\Middleware;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;

class SessionMiddleware implements MiddlewareInterface
{
    private SessionManager $sessionManager;
    private array $noUpdateRoutes;

    public function __construct(SessionManager $sessionManager, array $noUpdateRoutes = [])
    {
        $this->sessionManager = $sessionManager;
        $this->noUpdateRoutes = $noUpdateRoutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        // Determine if we should update activity
        $updateActivity = true;
        foreach ($this->noUpdateRoutes as $route) {
            if (strpos($request->getUri(), $route) !== false) {
                $updateActivity = false;

                break;
            }
        }

        try {
            // Initialize session
            $this->sessionManager->init($updateActivity);

            // Add session data to request
            if ($this->sessionManager->has('user')) {
                $request->setAttribute('user', $this->sessionManager->getUser());
                $request->setAttribute('sessionLifetime', $this->sessionManager->getRemainingLifetime());
            }
        } catch (\Exception $e) {
            // Session initialization failed
            if (strpos($request->getUri(), '/api/') === 0) {
                return (new Response())->json([
                    'error' => 'Session expired',
                    'message' => $e->getMessage(),
                ], 401);
            }

            return (new Response())->redirect('/login');
        }

        return $next($request);
    }
}
