<?php

// File: config/database.php

use App\Core\Config\ConfigManager;

$config = ConfigManager::getInstance();

return [
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => $config->env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    */

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => $config->env('DATABASE_URL'),
'database' => $config->env('DB_DATABASE', dirname(__DIR__) . '/database/database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => $config->env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => $config->env('DATABASE_URL'),
            'host' => $config->env('DB_HOST', '127.0.0.1'),
            'port' => $config->env('DB_PORT', '3306'),
            'database' => $config->env('DB_DATABASE', 'harmony_hrms'),
            'username' => $config->env('DB_USERNAME', 'root'),
            'password' => $config->env('DB_PASSWORD', ''),
            'unix_socket' => $config->env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => $config->env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => $config->env('DATABASE_URL'),
            'host' => $config->env('DB_HOST', '127.0.0.1'),
            'port' => $config->env('DB_PORT', '5432'),
            'database' => $config->env('DB_DATABASE', 'harmony_hrms'),
            'username' => $config->env('DB_USERNAME', 'root'),
            'password' => $config->env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',
];
