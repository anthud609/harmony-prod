<?php
// File: app/Modules/IAM/Services/AuthService.php

namespace App\Modules\IAM\Services;

use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\User; // Add this import

class AuthService
{
    use LoggerTrait;

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
            'last_login_at' => date('Y-m-d H:i:s'),
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
        $this->logInfo('User logged in', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Log failed login attempt
     */
    public function logFailedLogin(string $username): void
    {
        $this->logWarning('Failed login attempt', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Log logout
     */
    public function logLogout(string $username): void
    {
        $this->logInfo('User logged out', [
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    }

    /**
     * Update theme preference
     */
    public function updateThemePreference(string $theme): bool
    {
        $validThemes = ['light', 'dark', 'system'];
        
        if (!in_array($theme, $validThemes)) {
            $this->logWarning('Invalid theme preference', ['theme' => $theme]);
            return false;
        }
        
        // In a real application, update the user's theme in database
        $this->logInfo('Theme preference updated', ['theme' => $theme]);
        
        return true;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllNotificationsRead(string $userId): void
    {
        // In a real application, update database
        $this->logInfo('All notifications marked as read', ['userId' => $userId]);
    }
}