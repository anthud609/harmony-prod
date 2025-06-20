<?php

// File: app/Core/Container/Providers/ControllerServiceProvider.php

namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Dashboard\Controllers\DashboardController;
use App\Core\Dashboard\Repositories\ActivityRepository;
use App\Core\Dashboard\Repositories\DashboardRepository;
use App\Core\Dashboard\Repositories\WidgetRepository;
use App\Core\Dashboard\Services\DashboardService;
use App\Modules\IAM\Controllers\AuthController;
use App\Modules\IAM\Services\AuthService;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Repositories
            DashboardRepository::class => \DI\autowire(),
            ActivityRepository::class => \DI\autowire(),
            WidgetRepository::class => \DI\autowire(),

            // Services
            DashboardService::class => \DI\autowire(),
            AuthService::class => \DI\autowire(),

            // Controllers - let autowiring handle everything
            DashboardController::class => \DI\autowire(),
            AuthController::class => \DI\autowire(),
        ];
    }
}
