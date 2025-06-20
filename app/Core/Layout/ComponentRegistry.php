<?php

// File: app/Core/Layout/ComponentRegistry.php

namespace App\Core\Layout;

use DI\Container;

/**
 * Component registry that allows dynamic registration
 * Fixes the Open/Closed violation
 */
class ComponentRegistry
{
    private Container $container;
    private array $components = [];
    private array $aliases = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register a component class
     */
    public function register(string $name, string $className): self
    {
        if (! class_exists($className)) {
            throw new \InvalidArgumentException("Component class does not exist: {$className}");
        }

        $this->components[$name] = $className;

        return $this;
    }

    /**
     * Register multiple components at once
     */
    public function registerMany(array $components): self
    {
        foreach ($components as $name => $className) {
            $this->register($name, $className);
        }

        return $this;
    }

    /**
     * Register an alias for a component
     */
    public function alias(string $alias, string $name): self
    {
        $this->aliases[$alias] = $name;

        return $this;
    }

    /**
     * Create a component instance
     */
    public function create(string $name): object
    {
        // Check if it's an alias
        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        if (! isset($this->components[$name])) {
            throw new \InvalidArgumentException("Unknown component: {$name}");
        }

        // Use DI container to create instance with dependencies
        return $this->container->get($this->components[$name]);
    }

    /**
     * Check if a component is registered
     */
    public function has(string $name): bool
    {
        return isset($this->components[$name]) || isset($this->aliases[$name]);
    }

    /**
     * Get all registered component names
     */
    public function getRegisteredNames(): array
    {
        return array_keys($this->components);
    }
}
