<?php

// File: app/Modules/IAM/Controllers/AuthController.php

namespace App\Modules\IAM\Controllers;

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

    public function showLogin(): void
    {
        // simply render the login form
        require __DIR__ . '/../Views/login.php';
    }

    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->authService->authenticate($username, $password);

        if ($user !== null) {
            // CRITICAL: Regenerate session ID to prevent session fixation
            $this->sessionManager->regenerate(true);

            // Set user data in session
            $this->sessionManager->set('user', $user);

            // Set theme preference in session for immediate use
            $this->sessionManager->set('theme', $user['preferredTheme']);

            // Log successful login
            $this->authService->logSuccessfulLogin($username);

            header('Location: /dashboard');
            exit;
        }

        // Log failed login attempt
        $this->authService->logFailedLogin($username);

        // on failure, back to login with an error
        $this->sessionManager->set('flash_error', 'Invalid credentials.');
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        // Get user info before destroying session
        $user = $this->sessionManager->get('user');

        if ($user) {
            // Log logout
            $this->authService->logLogout($user['username'] ?? 'unknown');
        }

        // Securely destroy session
        $this->sessionManager->destroy();

        header('Location: /login');
        exit;
    }

    /**
     * Update user preferences (e.g., theme)
     */
    public function updatePreferences(): void
    {
        if (! $this->sessionManager->has('user')) {
            header('Location: /login');
            exit;
        }

        $theme = $_POST['theme'] ?? 'system';

        // Validate and update theme
        if ($this->authService->updateThemePreference($theme)) {
            $user = $this->sessionManager->get('user');
            $user['preferredTheme'] = $theme;
            $this->sessionManager->set('user', $user);
            $this->sessionManager->set('theme', $theme);
        }

        // Return JSON response for AJAX requests
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'theme' => $theme]);
            exit;
        }

        // Otherwise redirect back
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard'));
        exit;
    }

    /**
     * Mark notifications as read
     */
    public function markNotificationsRead(): void
    {
        if (! $this->sessionManager->has('user')) {
            header('Location: /login');
            exit;
        }

        // Reset notification count
        $user = $this->sessionManager->get('user');
        $user['notificationCount'] = 0;
        $this->sessionManager->set('user', $user);

        // In a real application, you would update this in database
        $this->authService->markAllNotificationsRead($user['id']);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }

    private function isAjaxRequest(): bool
    {
        return ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
