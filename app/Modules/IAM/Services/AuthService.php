<?php
// File: app/Modules/IAM/Services/AuthService.php
namespace App\Modules\IAM\Services;

class AuthService
{
    // Demo users - in production this would be from database
    private array $users = [
        'alice_admin@email.com' => [
            'password' => 'secret',
            'role' => 'admin',
            'firstName' => 'Alice',
            'lastName' => 'Johnson',
            'jobTitle' => 'System Administrator',
            'preferredTheme' => 'dark',
            'notificationCount' => 5
        ],
        'bob_editor@email.com' => [
            'password' => 'secret',
            'role' => 'editor',
            'firstName' => 'Bob',
            'lastName' => 'Smith',
            'jobTitle' => 'Content Manager',
            'preferredTheme' => 'light',
            'notificationCount' => 3
        ],
        'charlie_user@email.com' => [
            'password' => 'secret',
            'role' => 'user',
            'firstName' => 'Charlie',
            'lastName' => 'Brown',
            'jobTitle' => 'Sales Representative',
            'preferredTheme' => 'system',
            'notificationCount' => 12
        ],
    ];
    
    /**
     * Authenticate user
     */
    public function authenticate(string $username, string $password): ?array
    {
        if (!isset($this->users[$username]) || $this->users[$username]['password'] !== $password) {
            return null;
        }
        
        return [
            'id' => array_search($username, array_keys($this->users)) + 1,
            'username' => $username,
            'role' => $this->users[$username]['role'],
            'firstName' => $this->users[$username]['firstName'],
            'lastName' => $this->users[$username]['lastName'],
            'jobTitle' => $this->users[$username]['jobTitle'],
            'preferredTheme' => $this->users[$username]['preferredTheme'],
            'notificationCount' => $this->users[$username]['notificationCount'],
            'loginTime' => time(),
            'lastActivity' => time()
        ];
    }
    
    /**
     * Log successful login
     */
    public function logSuccessfulLogin(string $username): void
    {
        error_log(sprintf(
            'Successful login: User %s from IP %s',
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }
    
    /**
     * Log failed login
     */
    public function logFailedLogin(string $username): void
    {
        error_log(sprintf(
            'Failed login attempt: Username %s from IP %s',
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }
    
    /**
     * Log logout
     */
    public function logLogout(string $username): void
    {
        error_log(sprintf(
            'User logout: %s from IP %s',
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
    }
    
    /**
     * Update theme preference
     */
    public function updateThemePreference(string $theme): bool
    {
        // Validate theme value
        return in_array($theme, ['light', 'dark', 'system']);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(int $userId): void
    {
        // In production, update database
        // For now, this is a no-op
    }
}