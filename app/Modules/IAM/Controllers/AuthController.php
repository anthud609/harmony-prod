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
        // VERY basic “demo” check — swap in your real user lookup here
        $users = [
            'alice_admin@email.com'  => ['password' => 'secret', 'role' => 'admin'],
            'bob_editor'   => ['password' => 'secret', 'role' => 'editor'],
            'charlie_user' => ['password' => 'secret', 'role' => 'user'],
        ];

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (isset($users[$username]) && $users[$username]['password'] === $password) {
            $_SESSION['user'] = [
                'id'       => array_search($username, array_keys($users)) + 1,
                'username' => $username,
                'role'     => $users[$username]['role'],
            ];
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
}
