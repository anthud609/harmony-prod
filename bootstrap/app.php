<?php

// File: bootstrap/app.php
// Manual bootstrap for the application

// Base path
$basePath = dirname(__DIR__);

// First, require composer autoload
require $basePath . '/vendor/autoload.php';
// Initialize database
require __DIR__ . '/database.php';
// Register a simple PSR-4 autoloader for App namespace
spl_autoload_register(function ($class) use ($basePath) {
    // Only handle App namespace
    if (strpos($class, 'App\\') !== 0) {
        return;
    }

    // Convert namespace to file path
    $file = $basePath . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load helper files in correct order
$helpers = [
    'config.php',
    'csrf.php',
    'xss.php',
    'logger.php',
];

foreach ($helpers as $helper) {
    $helperPath = $basePath . '/app/Core/Helpers/' . $helper;
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

// Initialize environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable($basePath);
    $dotenv->load();
} catch (Exception $e) {
    die('Error loading .env file: ' . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

return $basePath;
