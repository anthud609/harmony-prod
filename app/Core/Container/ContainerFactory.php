<?php
// File: app/Core/Container/ContainerFactory.php
namespace App\Core\Container;

use DI\Container;
use DI\ContainerBuilder;
use App\Core\Container\Providers\SecurityServiceProvider;
use App\Core\Container\Providers\LayoutServiceProvider;
use App\Core\Container\Providers\ControllerServiceProvider;
use App\Core\Container\Providers\ApiServiceProvider;

class ContainerFactory
{
    private static ?Container $container = null;
    
    /**
     * Create and configure the DI container
     */
    public static function create(): Container
    {
        if (self::$container !== null) {
            return self::$container;
        }
        
        $containerBuilder = new ContainerBuilder();
        
        // Enable compilation for better performance in production
        if (getenv('APP_ENV') === 'production') {
            $containerBuilder->enableCompilation(__DIR__ . '/../../../storage/cache/di');
            $containerBuilder->writeProxiesToFile(true, __DIR__ . '/../../../storage/cache/di/proxies');
        }
        
        // Add definitions from service providers
        $providers = [
            new SecurityServiceProvider(),
            new LayoutServiceProvider(),
            new ControllerServiceProvider(),
            new ApiServiceProvider(),
        ];
        
        foreach ($providers as $provider) {
            $containerBuilder->addDefinitions($provider->getDefinitions());
        }
        
        self::$container = $containerBuilder->build();
        
        return self::$container;
    }
    
    /**
     * Get the container instance (singleton)
     */
    public static function getInstance(): Container
    {
        if (self::$container === null) {
            self::$container = self::create();
        }
        
        return self::$container;
    }
    
    /**
     * Reset the container (mainly for testing)
     */
    public static function reset(): void
    {
        self::$container = null;
    }
}