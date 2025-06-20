<?php
// File: app/Core/Layout/LayoutRenderer.php (UPDATED)
namespace App\Core\Layout;

/**
 * Responsible for rendering layouts with their content
 */
class LayoutRenderer
{
    private ViewRenderer $viewRenderer;
    private string $layoutsPath;
    
    public function __construct(ViewRenderer $viewRenderer, string $layoutsPath = null)
    {
        $this->viewRenderer = $viewRenderer;
        $this->layoutsPath = $layoutsPath ?? __DIR__ . '/Layouts';
    }
    
    /**
     * Render a layout with content
     * Pass the layout manager instance so components can be rendered
     */
    public function renderWithLayout(string $layout, string $content, array $data = [], ?LayoutManager $layoutManager = null): string
    {
        $layoutFile = $this->layoutsPath . '/' . ucfirst($layout) . 'Layout.php';
        
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout file not found: {$layoutFile}");
        }
        
        // Add content and layout manager to data
        $data['content'] = $content;
        $data['layoutManager'] = $layoutManager;
        
        return $this->viewRenderer->render($layoutFile, $data);
    }
    
    /**
     * Display a layout with content
     */
    public function displayWithLayout(string $layout, string $content, array $data = [], ?LayoutManager $layoutManager = null): void
    {
        echo $this->renderWithLayout($layout, $content, $data, $layoutManager);
    }
}