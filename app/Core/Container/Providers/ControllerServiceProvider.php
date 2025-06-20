<?php
// File: app/Core/Container/Providers/ControllerServiceProvider.php
namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Dashboard\Controllers\DashboardController;
use App\Modules\IAM\Controllers\AuthController;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;
use App\Core\Dashboard\Services\DashboardService;
use App\Modules\IAM\Services\AuthService;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Services
            DashboardService::class => \DI\autowire(),
            AuthService::class => \DI\autowire(), // AuthService doesn't need constructor params
            
            // Controllers with injected dependencies
            DashboardController::class => \DI\autowire()
                ->constructorParameter('layoutManager', \DI\get(LayoutManager::class))
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class))
                ->constructorParameter('dashboardService', \DI\get(DashboardService::class)),
                
            AuthController::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class))
                ->constructorParameter('authService', \DI\get(AuthService::class)),
        ];
    }
}