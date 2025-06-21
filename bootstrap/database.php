<?php
// File: bootstrap/database.php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Load configuration
$config = require __DIR__ . '/../config/database.php';

// Create new Capsule instance
$capsule = new Capsule;

// Add database connection
$defaultConnection = $config['default'] ?? 'mysql';
$connectionConfig = $config['connections'][$defaultConnection] ?? [];

// Add connection with default values if config is missing
$capsule->addConnection(array_merge([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'harmony_hrms',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
], $connectionConfig));

// Set the event dispatcher
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Return the capsule instance
return $capsule;