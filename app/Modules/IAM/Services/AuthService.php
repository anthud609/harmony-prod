<?php

// File: app/Modules/IAM/Services/AuthService.php

namespace App\Modules\IAM\Services;

use App\Core\Traits\LoggerTrait;

class AuthService
{
    use LoggerTrait;

    // Demo users - in production this would be from database
    private array $users = [
        'alice_admin@email.com' => [
            'password' => 'secret',
            'role' => 'admin',
            'firstName' => 'Alice',
            'lastName' => 'Johnson',
            'jobTitle' => 'System Administrator',
            'preferredTheme' => 'dark',
            'notificationCount' => 5,
        ],
        'bob_editor@email.com' => [
            'password' => 'secret',
            'role' => 'editor',
            'firstName' => 'Bob',
            'lastName' => 'Smith',
            'jobTitle' => 'Content Manager',
            'preferredTheme' => 'light',
            'notificationCount' => 3,
        ],
        'charlie_user@email.com' => [
            'password' => 'secret',
            'role' => 'user',
            'firstName' => 'Charlie',
            'lastName' => 'Brown',
            'jobTitle' => 'Sales Representative',
            'preferredTheme' => 'system',
            'notificationCount' => 12,
        ],
    ];

    /**
     * Authenticate user
     */
    public function authenticate(string $username, string $password): ?array
    {
        $this->logDebug('Authentication attempt', ['username' => $username]);

        if (! isset($this->users[$username]) || $this->users[$username]['password'] !== $password) {
            $this->logWarning('Authentication failed', [
                'username' => $username,
                'reason' => ! isset($this->users[$username]) ? 'user_not_found' : 'invalid_password',
            ]);

            return null;
        }

        $userData = [
            'id' => array_search($username, array_keys($this->users)) + 1,
            'username' => $username,
            'role' => $this->users[$username]['role'],
            'firstName' => $this->users[$username]['firstName'],
            'lastName' => $this->users[$username]['lastName'],
            'jobTitle' => $this->users[$username]['jobTitle'],
            'preferredTheme' => $this->users[$username]['preferredTheme'],
            'notificationCount' => $this->users[$username]['notificationCount'],
            'loginTime' => time(),
            'lastActivity' => time(),
        ];

        $this->logInfo('Authentication successful', [
            'username' => $username,
            'userId' => $userData['id'],
            'role' => $userData['role'],
        ]);

        return $userData;
    }

    /**
     * Log successful login
     */
    public function logSuccessfulLogin(string $username): void
    {
        $this->logInfo('User login successful', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Log failed login
     */
    public function logFailedLogin(string $username): void
    {
        $this->logWarning('Login attempt failed', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Log logout
     */
    public function logLogout(string $username): void
    {
        $this->logInfo('User logout', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    }

    /**
     * Update theme preference
     */
    public function updateThemePreference(string $theme): bool
    {
        // Validate theme value
        $valid = in_array($theme, ['light', 'dark', 'system']);

        if ($valid) {
            $this->logDebug('Theme preference updated', ['theme' => $theme]);
        } else {
            $this->logWarning('Invalid theme preference attempted', ['theme' => $theme]);
        }

        return $valid;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(int $userId): void
    {
        // In production, update database
        $this->logDebug('Notifications marked as read', ['userId' => $userId]);
    }
}
