<?php

// File: app/Core/Layout/ComponentFactory.php

namespace App\Core\Layout;

use DI\Container;

class ComponentFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Create a component instance by name
     */
    public function create(string $componentName): object
    {
        $componentMap = [
            'header' => Components\Header::class,
            'sidebar' => Components\Sidebar::class,
            'scripts' => Components\Scripts::class,
            'userMenu' => Components\UserMenu::class,
            'messages' => Components\Messages::class,
            'notifications' => Components\Notifications::class,
            'commandPalette' => Components\CommandPalette::class,
            'globalScripts' => Components\GlobalScripts::class,
            'pageHeader' => Components\PageHeader::class,
        ];

        if (! isset($componentMap[$componentName])) {
            throw new \InvalidArgumentException("Unknown component: {$componentName}");
        }

        return $this->container->get($componentMap[$componentName]);
    }
}
