<?php
// File: app/Core/Container/Providers/ApiServiceProvider.php
namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Api\Controllers\SessionController;
use App\Core\Security\SessionManager;

class ApiServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            SessionController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class)),
        ];
    }
}