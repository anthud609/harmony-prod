<?php
// File: bootstrap/database.php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// Add database connection
$capsule->addConnection([
    'driver'    => config('database.connections.mysql.driver', 'mysql'),
    'host'      => config('database.connections.mysql.host', '127.0.0.1'),
    'port'      => config('database.connections.mysql.port', '3306'),
    'database'  => config('database.connections.mysql.database', 'harmony_hrms'),
    'username'  => config('database.connections.mysql.username', 'root'),
    'password'  => config('database.connections.mysql.password', ''),
    'charset'   => config('database.connections.mysql.charset', 'utf8mb4'),
    'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
    'prefix'    => config('database.connections.mysql.prefix', ''),
]);

// Set the event dispatcher used by Eloquent models
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();