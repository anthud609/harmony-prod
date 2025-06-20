<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();

use App\Modules\IAM\Controllers\AuthController;
use App\Core\Dashboard\Controllers\DashboardController;

// (re)register your autoloader if needed—Composer should already handle it.

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// simple router map → [ ControllerClass, method ]
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

// guard
if ($requestPath !== '/login' && $requestPath !== '/login.post' && !isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// dispatch
if (isset($routes[$requestPath])) {
    [$class, $method] = $routes[$requestPath];
    (new $class)->{$method}();
} else {
    header("HTTP/1.1 404 Not Found");
    echo '404 — Not Found';
}
