<?php
// File: app/Core/Container/Providers/DatabaseServiceProvider.php

namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Capsule::class => \DI\factory(function () {
                require_once dirname(__DIR__, 4) . '/bootstrap/database.php';
                return Capsule::connection();
            }),
            
            'db' => \DI\get(Capsule::class),
        ];
    }
}