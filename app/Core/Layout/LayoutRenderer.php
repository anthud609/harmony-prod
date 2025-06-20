<?php

// File: app/Core/Layout/LayoutRenderer.php
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
     */
    public function renderWithLayout(string $layout, string $content, array $data = []): string
    {
        $layoutFile = $this->layoutsPath . '/' . ucfirst($layout) . 'Layout.php';
        
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout file not found: {$layoutFile}");
        }
        
        // Add content to data
        $data['content'] = $content;
        
        return $this->viewRenderer->render($layoutFile, $data);
    }
    
    /**
     * Display a layout with content
     */
    public function displayWithLayout(string $layout, string $content, array $data = []): void
    {
        echo $this->renderWithLayout($layout, $content, $data);
    }
}