<?php

// File: app/Core/Layout/ViewRenderer.php

namespace App\Core\Layout;

/**
 * Responsible ONLY for rendering views
 */
class ViewRenderer
{
    /**
     * Render a view file with provided data
     */
    public function render(string $viewPath, array $data = []): string
    {
        if (! file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        // Extract data to make available in view
        extract($data);

        // Capture output
        ob_start();
        require $viewPath;

        return ob_get_clean();
    }

    /**
     * Render and output a view directly
     */
    public function display(string $viewPath, array $data = []): void
    {
        echo $this->render($viewPath, $data);
    }
}
