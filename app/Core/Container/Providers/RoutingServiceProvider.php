<?php
// File: app/Core/Container/Providers/RoutingServiceProvider.php
namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Routing\Router;
use DI\Container;

class RoutingServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            Router::class => \DI\autowire()
                ->constructorParameter('container', \DI\get(Container::class)),
        ];
    }
}