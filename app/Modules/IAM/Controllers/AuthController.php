<?php
namespace App\Modules\IAM\Controllers;

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
            $_SESSION['user'] = [
                'id'                => array_search($username, array_keys($users)) + 1,
                'username'          => $username,
                'role'              => $users[$username]['role'],
                'firstName'         => $users[$username]['firstName'],
                'lastName'          => $users[$username]['lastName'],
                'jobTitle'          => $users[$username]['jobTitle'],
                'preferredTheme'    => $users[$username]['preferredTheme'],
                'notificationCount' => $users[$username]['notificationCount']
            ];
            
            // Set theme preference in session for immediate use
            $_SESSION['theme'] = $users[$username]['preferredTheme'];
            
            header('Location: /dashboard');
            exit;
        }

        // on failure, back to login with an error
        $_SESSION['flash_error'] = 'Invalid credentials.';
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    /**
     * Update user preferences (e.g., theme)
     */
    public function updatePreferences(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        $theme = $_POST['theme'] ?? 'system';
        
        // Validate theme value
        if (in_array($theme, ['light', 'dark', 'system'])) {
            $_SESSION['user']['preferredTheme'] = $theme;
            $_SESSION['theme'] = $theme;
            
            // In a real application, you would save this to database
            // $this->userRepository->updateTheme($_SESSION['user']['id'], $theme);
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
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        
        // Reset notification count
        $_SESSION['user']['notificationCount'] = 0;
        
        // In a real application, you would update this in database
        // $this->notificationRepository->markAllRead($_SESSION['user']['id']);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }
}