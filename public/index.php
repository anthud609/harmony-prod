<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use App\Modules\IAM\Controllers\AuthController;
use App\Core\Dashboard\Controllers\DashboardController;

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ──────────────────────────────────────────────────────────────────────────
// If already logged in, redirect any “/” or “/login” request to /dashboard
// ──────────────────────────────────────────────────────────────────────────
if (isset($_SESSION['user']) && in_array($requestPath, ['/', '/login'], true)) {
    header('Location: /dashboard');
    exit;
}

// ──────────────────────────────────────────────────────────────────────────
// Route definitions
// ──────────────────────────────────────────────────────────────────────────
$routes = [
    '/'           => [AuthController::class,    'showLogin'],
    '/login'      => [AuthController::class,    'showLogin'],
    '/login.post' => [AuthController::class,    'login'],
    '/logout'     => [AuthController::class,    'logout'],
    '/dashboard'  => [DashboardController::class, 'index'],
];

// redirect POST /login to /login.post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestPath === '/login') {
    $requestPath = '/login.post';
}

// ──────────────────────────────────────────────────────────────────────────
// Auth guard: everything except login routes requires a user
// ──────────────────────────────────────────────────────────────────────────
if (!in_array($requestPath, ['/login', '/login.post'], true) && !isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// ──────────────────────────────────────────────────────────────────────────
// Dispatch
// ──────────────────────────────────────────────────────────────────────────
if (isset($routes[$requestPath])) {
    [$class, $method] = $routes[$requestPath];
    (new $class)->{$method}();
} else {
    header("HTTP/1.1 404 Not Found");
    echo '404 — Not Found';
}
