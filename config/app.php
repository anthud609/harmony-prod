<?php
// File: config/app.php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'Harmony HRMS'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */
    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */
    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => 'en',
    'available_locales' => ['en', 'es', 'fr', 'de', 'pt'],

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    */
    'version' => '1.0.0',
    
    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    */
    'maintenance' => [
        'enabled' => env('APP_MAINTENANCE', false),
        'message' => env('APP_MAINTENANCE_MESSAGE', 'We are currently performing scheduled maintenance.'),
        'retry_after' => env('APP_MAINTENANCE_RETRY', 3600),
        'allowed_ips' => explode(',', env('APP_MAINTENANCE_ALLOWED_IPS', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        App\Core\Container\Providers\LoggingServiceProvider::class,
        App\Core\Container\Providers\SecurityServiceProvider::class,
        App\Core\Container\Providers\LayoutServiceProvider::class,
        App\Core\Container\Providers\ControllerServiceProvider::class,
        App\Core\Container\Providers\ApiServiceProvider::class,
        App\Core\Container\Providers\RoutingServiceProvider::class,
    ],
];