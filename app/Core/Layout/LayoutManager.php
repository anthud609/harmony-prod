<?php
namespace App\Core\Layout;

class LayoutManager
{
    protected array $data = [];
    protected string $layout = 'main';
    
    public function __construct()
    {
        // Set default data
        $this->data = [
            'title' => 'Harmony HRMS',
            'user' => $_SESSION['user'] ?? null
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
        extract($data);
        
        $componentFile = __DIR__ . "/Components/" . ucfirst($component) . ".php";
        require $componentFile;
    }
}