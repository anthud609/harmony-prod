<?php
// File: app/Core/Layout/LayoutRenderer.php
namespace App\Core\Layout;

/**
 * Responsible for rendering layouts with their content
 */
class LayoutRenderer
{
    private ViewRenderer $viewRenderer;
    private ComponentRenderer $componentRenderer;
    private string $layoutsPath;
    
    public function __construct(ViewRenderer $viewRenderer, ComponentRenderer $componentRenderer = null, string $layoutsPath = null)
    {
        $this->viewRenderer = $viewRenderer;
        $this->componentRenderer = $componentRenderer;
        $this->layoutsPath = $layoutsPath ?? __DIR__ . '/Layouts';
    }
    
    /**
     * Set the component renderer (for dependency injection)
     */
    public function setComponentRenderer(ComponentRenderer $componentRenderer): void
    {
        $this->componentRenderer = $componentRenderer;
    }
    
    /**
     * Render a layout with content
     */
    public function renderWithLayout(string $layout, string $content, array $data = []): string
    {
        $layoutFile = $this->layoutsPath . '/' . ucfirst($layout) . 'Layout.php';
        
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout file not found: {$layoutFile}");
        }
        
        // Add content to data
        $data['content'] = $content;
        
        // Create a layout context that includes the component method
        $layoutContext = new class($this->componentRenderer, $data) {
            private ?ComponentRenderer $componentRenderer;
            private array $data;
            
            public function __construct(?ComponentRenderer $componentRenderer, array $data)
            {
                $this->componentRenderer = $componentRenderer;
                $this->data = $data;
            }
            
            /**
             * Render a component within the layout
             */
            public function component(string $componentName, array $componentData = []): void
            {
                if (!$this->componentRenderer) {
                    throw new \RuntimeException("ComponentRenderer not available");
                }
                
                // Merge layout data with component-specific data
                $mergedData = array_merge($this->data, $componentData);
                $this->componentRenderer->render($componentName, $mergedData);
            }
        };
        
        // Capture the layout output with the context
        return $this->renderLayoutWithContext($layoutFile, $data, $layoutContext);
    }
    
    /**
     * Render layout file with a specific context
     */
    private function renderLayoutWithContext(string $layoutFile, array $data, $context): string
    {
        // Extract data to make available in layout
        extract($data);
        
        // Make the context available as $this in the layout
        $renderLayout = function() use ($layoutFile, $data) {
            extract($data);
            require $layoutFile;
        };
        
        // Bind the closure to the context
        $boundRenderLayout = $renderLayout->bindTo($context, get_class($context));
        
        // Capture output
        ob_start();
        $boundRenderLayout();
        return ob_get_clean();
    }
    
    /**
     * Display a layout with content
     */
    public function displayWithLayout(string $layout, string $content, array $data = []): void
    {
        echo $this->renderWithLayout($layout, $content, $data);
    }
}