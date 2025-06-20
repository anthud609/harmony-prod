<?php

// File: app/Core/Layout/ComponentRenderer.php

namespace App\Core\Layout;

/**
 * Responsible for rendering components
 */
class ComponentRenderer
{
    private ComponentRegistry $registry;

    public function __construct(ComponentRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Render a component
     */
    public function render(string $componentName, array $data = []): void
    {
        $component = $this->registry->create($componentName);

        if (! method_exists($component, 'render')) {
            throw new \RuntimeException(
                "Component '{$componentName}' does not have a render method"
            );
        }

        $component->render($data);
    }
}
