<?php
// File: app/Core/Layout/LayoutManager.php (Updated)
namespace App\Core\Layout;

use App\Core\Layout\Components\Header;
use App\Core\Layout\Components\Sidebar;
use App\Core\Layout\Components\Scripts;
use App\Core\Layout\Components\UserMenu;
use App\Core\Layout\Components\Messages;
use App\Core\Layout\Components\Notifications;
use App\Core\Layout\Components\CommandPalette;
use App\Core\Layout\Components\GlobalScripts;
use App\Core\Layout\Components\PageHeader;

class LayoutManager
{
    protected array $data = [];
    protected string $layout = 'main';
    
    public function __construct()
    {
        // Set default data
        $this->data = [
            'title' => 'Harmony HRMS',
            'user' => $_SESSION['user'] ?? null,
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
        
        // Map component names to their class methods
        $componentMap = [
            'header' => [Header::class, 'render'],
            'sidebar' => [Sidebar::class, 'render'],
            'scripts' => [Scripts::class, 'render'],
            'userMenu' => [UserMenu::class, 'render'],
            'messages' => [Messages::class, 'renderDropdown'],
            'notifications' => [Notifications::class, 'renderDropdown'],
            'commandPalette' => [CommandPalette::class, 'render'],
            'globalScripts' => [GlobalScripts::class, 'render'],
            'pageHeader' => [PageHeader::class, 'render']
        ];
        
        if (isset($componentMap[$component])) {
            [$class, $method] = $componentMap[$component];
            $class::$method($data);
        } else {
            // Fallback to old file-based approach if component not in map
            $componentFile = __DIR__ . "/Components/" . ucfirst($component) . ".php";
            if (file_exists($componentFile)) {
                extract($data);
                require $componentFile;
            } else {
                throw new \Exception("Component '{$component}' not found");
            }
        }
    }
}