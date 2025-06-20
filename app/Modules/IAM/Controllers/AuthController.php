<?php
// File: app/Modules/IAM/Controllers/AuthController.php (FIXED)

namespace App\Modules\IAM\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\SessionManager;
use App\Modules\IAM\Services\AuthService;

class AuthController
{
    private SessionManager $sessionManager;
    private AuthService $authService;

    public function __construct(
        SessionManager $sessionManager,
        AuthService $authService
    ) {
        $this->sessionManager = $sessionManager;
        $this->authService = $authService;
    }

    public function showLogin(Request $request): Response
    {
        // Capture view output
        ob_start();
        require __DIR__ . '/../Views/login.php';
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    public function login(Request $request): Response
    {
        $username = $request->getPost('username', '');
        $password = $request->getPost('password', '');

        $user = $this->authService->authenticate($username, $password);

        if ($user !== null) {
            // Regenerate session ID to prevent session fixation
            $this->sessionManager->regenerate(true);

            // Set user data in session
            $this->sessionManager->set('user', $user);
            $this->sessionManager->set('theme', $user['preferredTheme']);

            // Log successful login
            $this->authService->logSuccessfulLogin($username);

            return (new Response())->redirect('/dashboard');
        }

        // Log failed login attempt
        $this->authService->logFailedLogin($username);

        // Set error message and redirect
        $this->sessionManager->set('flash_error', 'Invalid credentials.');
        return (new Response())->redirect('/login');
    }

    public function logout(Request $request): Response
    {
        // Get user info before destroying session
        $user = $this->sessionManager->get('user');

        if ($user) {
            $this->authService->logLogout($user['username'] ?? 'unknown');
        }

        // Securely destroy session
        $this->sessionManager->destroy();

        return (new Response())->redirect('/login');
    }

    public function updatePreferences(Request $request): Response
    {
        if (!$this->sessionManager->has('user')) {
            return (new Response())->redirect('/login');
        }

        $theme = $request->getPost('theme', 'system');

        // Validate and update theme
        if ($this->authService->updateThemePreference($theme)) {
            $user = $this->sessionManager->get('user');
            $user['preferredTheme'] = $theme;
            $this->sessionManager->set('user', $user);
            $this->sessionManager->set('theme', $theme);
        }

        // Return JSON response for AJAX requests
        if ($this->isAjaxRequest($request)) {
            return (new Response())->json(['success' => true, 'theme' => $theme]);
        }

        // Otherwise redirect back
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        return (new Response())->redirect($referer);
    }

    public function markNotificationsRead(Request $request): Response
    {
        if (!$this->sessionManager->has('user')) {
            return (new Response())->json(['error' => 'Not authenticated'], 401);
        }

        // Reset notification count
        $user = $this->sessionManager->get('user');
        $user['notificationCount'] = 0;
        $this->sessionManager->set('user', $user);

        // In a real application, you would update this in database
        $this->authService->markAllNotificationsRead($user['id']);

        return (new Response())->json(['success' => true, 'count' => 0]);
    }

    private function isAjaxRequest(Request $request): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
