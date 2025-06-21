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
        
        // Find user by username or email
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->where('status', 'active')
            ->first();
        
        if (!$user || !$user->verifyPassword($password)) {
            $this->logWarning('Authentication failed', [
                'username' => $username,
                'reason' => !$user ? 'user_not_found' : 'invalid_password',
            ]);
            return null;
        }
        
        // Update last login
        $user->update([
            'last_login_at' => now(),
            'notification_count' => $user->notifications()->where('is_read', false)->count(),
            'message_count' => $user->receivedMessages()->where('is_read', false)->count()
        ]);
        
        // Prepare user data for session
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->roles->first()->name ?? 'user',
            'roles' => $user->roles->pluck('name')->toArray(),
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'fullName' => $user->full_name,
            'initials' => $user->initials,
            'jobTitle' => $user->job_title,
            'preferredTheme' => $user->preferred_theme,
            'notificationCount' => $user->notification_count,
            'messageCount' => $user->message_count,
            'loginTime' => time(),
            'lastActivity' => time(),
            'favorites' => $user->favorites ?? [],
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray()
        ];
        
        $this->logInfo('Authentication successful', [
            'username' => $username,
            'userId' => $user->id,
            'role' => $userData['role'],
        ]);
        
        return $userData;
    }
    
    /**
     * Log successful login
     */
    public function logSuccessfulLogin(string $username): void
    {
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();
        
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'type' => 'auth',
                'module' => 'authentication',
                'action' => 'login',
                'description' => 'User logged in successfully',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'performed_at' => now()
            ]);
        }
        
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
