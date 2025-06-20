<?php
namespace App\Modules\IAM\Controllers;

use App\Core\Security\SessionManager;

class AuthController
{
    public function showLogin(): void
    {
        // simply render the login form
        require __DIR__ . '/../Views/login.php';
    }

    public function login(): void
    {
        // Enhanced demo users with additional fields
        $users = [
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

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($users[$username]) && $users[$username]['password'] === $password) {
            // CRITICAL: Regenerate session ID to prevent session fixation
            SessionManager::regenerate(true);
            
            // Set user data in session
            SessionManager::set('user', [
                'id'                => array_search($username, array_keys($users)) + 1,
                'username'          => $username,
                'role'              => $users[$username]['role'],
                'firstName'         => $users[$username]['firstName'],
                'lastName'          => $users[$username]['lastName'],
                'jobTitle'          => $users[$username]['jobTitle'],
                'preferredTheme'    => $users[$username]['preferredTheme'],
                'notificationCount' => $users[$username]['notificationCount'],
                'loginTime'         => time(),
                'lastActivity'      => time()
            ]);
            
            // Set theme preference in session for immediate use
            SessionManager::set('theme', $users[$username]['preferredTheme']);
            
            // Log successful login (in production, log to file/database)
            error_log(sprintf(
                'Successful login: User %s from IP %s',
                $username,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));
            
            header('Location: /dashboard');
            exit;
        }

        // Log failed login attempt
        error_log(sprintf(
            'Failed login attempt: Username %s from IP %s',
            $username,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));

        // on failure, back to login with an error
        SessionManager::set('flash_error', 'Invalid credentials.');
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        // Get user info before destroying session
        $user = SessionManager::get('user');
        
        if ($user) {
            // Log logout
            error_log(sprintf(
                'User logout: %s from IP %s',
                $user['username'] ?? 'unknown',
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));
        }
        
        // Securely destroy session
        SessionManager::destroy();
        
        header('Location: /login');
        exit;
    }
    
    /**
     * Update user preferences (e.g., theme)
     */
    public function updatePreferences(): void
    {
        if (!SessionManager::has('user')) {
            header('Location: /login');
            exit;
        }
        
        $theme = $_POST['theme'] ?? 'system';
        
        // Validate theme value
        if (in_array($theme, ['light', 'dark', 'system'])) {
            $user = SessionManager::get('user');
            $user['preferredTheme'] = $theme;
            SessionManager::set('user', $user);
            SessionManager::set('theme', $theme);
            
            // In a real application, you would save this to database
            // $this->userRepository->updateTheme($user['id'], $theme);
        }
        
        // Return JSON response for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
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
        if (!SessionManager::has('user')) {
            header('Location: /login');
            exit;
        }
        
        // Reset notification count
        $user = SessionManager::get('user');
        $user['notificationCount'] = 0;
        SessionManager::set('user', $user);
        
        // In a real application, you would update this in database
        // $this->notificationRepository->markAllRead($user['id']);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }
}