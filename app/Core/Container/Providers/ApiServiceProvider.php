<?php

// File: app/Core/Container/Providers/ApiServiceProvider.php

namespace App\Core\Container\Providers;

use App\Core\Api\Controllers\MessagesController;
use App\Core\Api\Controllers\NotificationsController;
use App\Core\Api\Controllers\SearchController;
use App\Core\Api\Controllers\SessionController;
use App\Core\Container\ServiceProviderInterface;
use App\Core\Security\SessionManager;

class ApiServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            SessionController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class)),
            SearchController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class)),
            MessagesController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class)),
            NotificationsController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class)),
        ];
    }
}
