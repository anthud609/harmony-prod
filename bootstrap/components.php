<?php

/**
 * This file can be used to register additional components from modules
 * without modifying the core LayoutServiceProvider
 */

use App\Core\Container\ContainerFactory;
use App\Core\Layout\ComponentRegistry;

return function () {
    $container = ContainerFactory::getInstance();
    $registry = $container->get(ComponentRegistry::class);

    // Auto-discover and register components from modules
    $moduleComponentRegistrations = [
        // Add your module component registrations here
        // new \App\Modules\CustomModule\Components\CustomComponentRegistration(),
    ];

    foreach ($moduleComponentRegistrations as $registration) {
        if ($registration instanceof \App\Core\Layout\ComponentRegistration) {
            $registration->registerComponents($registry);
        }
    }
};
