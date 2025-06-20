<?php
// File: app/Core/Layout/LayoutManager.php
namespace App\Core\Layout;

use App\Core\Security\SessionManager;

class LayoutManager
{
    protected array $data = [];
    protected string $layout = 'main';
    protected SessionManager $sessionManager;
    protected ComponentFactory $componentFactory;
    
    public function __construct(
        SessionManager $sessionManager,
        ComponentFactory $componentFactory
    ) {
        $this->sessionManager = $sessionManager;
        $this->componentFactory = $componentFactory;
        
        // Set default data using injected SessionManager
        $this->data = [
            'title' => 'Harmony HRMS',
            'user' => $this->sessionManager->getUser(),
            'breadcrumbs' => [],
            'pageActions' => [],
            'pageDescription' => '',
            'helpLink' => '#'
        ];
    }
    
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function setBreadcrumbs(array $breadcrumbs): self
    {
        $this->data['breadcrumbs'] = $breadcrumbs;
        return $this;
    }
    
    public function setPageActions(array $actions): self
    {
        $this->data['pageActions'] = $actions;
        return $this;
    }
    
    public function setPageDescription(string $description): self
    {
        $this->data['pageDescription'] = $description;
        return $this;
    }
    
    public function setHelpLink(string $link): self
    {
        $this->data['helpLink'] = $link;
        return $this;
    }
    
    public function render(string $view, array $viewData = []): void
    {
        $data = array_merge($this->data, $viewData);
        extract($data);
        
        // Start output buffering for the content
        ob_start();
        require $view;
        $content = ob_get_clean();
        
        // Include the layout with the content
        $layoutFile = __DIR__ . "/Layouts/" . ucfirst($this->layout) . "Layout.php";
        require $layoutFile;
    }
    
    public function component(string $component, array $componentData = []): void
    {
        $data = array_merge($this->data, $componentData);
        
        try {
            // Use the component factory to create and render the component
            $componentInstance = $this->componentFactory->create($component);
            
            // All components should have a render method
            if (method_exists($componentInstance, 'render')) {
                $componentInstance->render($data);
            } else {
                throw new \Exception("Component '{$component}' does not have a render method");
            }
        } catch (\Exception $e) {
            throw new \Exception("Error rendering component '{$component}': " . $e->getMessage());
        }
    }
}