<?php
// File: app/Core/Container/Providers/LayoutServiceProvider.php (Updated)
namespace App\Core\Container\Providers;

use App\Core\Container\ServiceProviderInterface;
use App\Core\Layout\LayoutManager;
use App\Core\Layout\ComponentRegistry;
use App\Core\Layout\Components\Header;
use App\Core\Layout\Components\Sidebar;
use App\Core\Layout\Components\Scripts;
use App\Core\Layout\Components\UserMenu;
use App\Core\Layout\Components\Messages;
use App\Core\Layout\Components\Notifications;
use App\Core\Layout\Components\CommandPalette;
use App\Core\Layout\Components\GlobalScripts;
use App\Core\Layout\Components\PageHeader;
use App\Core\Security\SessionManager;

class LayoutServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            // Component Registry - now configurable and extensible
            ComponentRegistry::class => \DI\factory(function ($container) {
                $registry = new ComponentRegistry($container);
                
                // Register core components
                $registry->registerMany([
                    'header' => Header::class,
                    'sidebar' => Sidebar::class,
                    'scripts' => Scripts::class,
                    'userMenu' => UserMenu::class,
                    'messages' => Messages::class,
                    'notifications' => Notifications::class,
                    'commandPalette' => CommandPalette::class,
                    'globalScripts' => GlobalScripts::class,
                    'pageHeader' => PageHeader::class,
                ]);
                
                // Register aliases for convenience
                $registry->alias('nav', 'sidebar');
                $registry->alias('js', 'scripts');
                
                return $registry;
            }),
            
            // Layout Manager with dependencies
            LayoutManager::class => \DI\autowire()
                ->constructorParameter('sessionManager', \DI\get(SessionManager::class))
                ->constructorParameter('componentRegistry', \DI\get(ComponentRegistry::class)),
            
            // Register all components (unchanged)
            Header::class => \DI\autowire()
                ->constructorParameter('messages', \DI\get(Messages::class))
                ->constructorParameter('notifications', \DI\get(Notifications::class))
                ->constructorParameter('commandPalette', \DI\get(CommandPalette::class))
                ->constructorParameter('userMenu', \DI\get(UserMenu::class)),
                
            Sidebar::class => \DI\autowire(),
            Scripts::class => \DI\autowire()
                ->constructorParameter('globalScripts', \DI\get(GlobalScripts::class)),
            UserMenu::class => \DI\autowire(),
            Messages::class => \DI\autowire(),
            Notifications::class => \DI\autowire(),
            CommandPalette::class => \DI\autowire(),
            GlobalScripts::class => \DI\autowire(),
            PageHeader::class => \DI\autowire(),
        ];
    }
}