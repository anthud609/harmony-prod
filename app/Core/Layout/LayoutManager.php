<?php
// File: app/Core/Layout/LayoutManager.php (Refactored)
namespace App\Core\Layout;

use App\Core\Security\SessionManager;

/**
 * Refactored LayoutManager - now acts as a facade/coordinator
 * instead of a God Object
 */
class LayoutManager
{
    private ViewRenderer $viewRenderer;
    private ViewDataContainer $dataContainer;
    private LayoutRenderer $layoutRenderer;
    private ComponentRenderer $componentRenderer;
    private string $currentLayout = 'main';
    
    public function __construct(
        SessionManager $sessionManager,
        ComponentRegistry $componentRegistry
    ) {
        // Initialize sub-components
        $this->viewRenderer = new ViewRenderer();
        $this->componentRenderer = new ComponentRenderer($componentRegistry);
        $this->layoutRenderer = new LayoutRenderer($this->viewRenderer, $this->componentRenderer);
        
        // Initialize data container with defaults
        $this->dataContainer = new ViewDataContainer([
            'title' => 'Harmony HRMS',
            'user' => $sessionManager->getUser(),
            'breadcrumbs' => [],
            'pageActions' => [],
            'pageDescription' => '',
            'helpLink' => '#'
        ]);
    }
    
    /**
     * Set the layout to use
     */
    public function setLayout(string $layout): self
    {
        $this->currentLayout = $layout;
        return $this;
    }
    
    /**
     * Add data to the view
     */
    public function with(array $data): self
    {
        $this->dataContainer->with($data);
        return $this;
    }
    
    /**
     * Set breadcrumbs
     */
    public function setBreadcrumbs(array $breadcrumbs): self
    {
        $this->dataContainer->set('breadcrumbs', $breadcrumbs);
        return $this;
    }
    
    /**
     * Set page actions
     */
    public function setPageActions(array $actions): self
    {
        $this->dataContainer->set('pageActions', $actions);
        return $this;
    }
    
    /**
     * Set page description
     */
    public function setPageDescription(string $description): self
    {
        $this->dataContainer->set('pageDescription', $description);
        return $this;
    }
    
    /**
     * Set help link
     */
    public function setHelpLink(string $link): self
    {
        $this->dataContainer->set('helpLink', $link);
        return $this;
    }
    
    /**
     * Render the view with layout
     */
    public function render(string $view, array $viewData = []): void
    {
        // Merge all data
        $data = array_merge($this->dataContainer->all(), $viewData);
        
        // Render the view content
        $content = $this->viewRenderer->render($view, $data);
        
        // Display with layout
        $this->layoutRenderer->displayWithLayout($this->currentLayout, $content, $data);
    }
    
    /**
     * Render a component
     */
    public function component(string $component, array $componentData = []): void
    {
        $data = array_merge($this->dataContainer->all(), $componentData);
        $this->componentRenderer->render($component, $data);
    }
}