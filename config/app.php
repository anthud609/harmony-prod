<?php
// File: config/app.php
// Simple configuration without using the config() helper to avoid circular dependency

return [
    'name' => $_ENV['APP_NAME'] ?? 'Harmony HRMS',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    'locale' => $_ENV['APP_LOCALE'] ?? 'en',
    'key' => $_ENV['APP_KEY'] ?? '',
    'cipher' => 'AES-256-CBC',
    'version' => '1.0.0',
];