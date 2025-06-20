<?php

// File: app/Modules/CustomModule/Components/CustomComponentRegistration.php (Example)

namespace App\Modules\CustomModule\Components;

use App\Core\Layout\ComponentRegistration;
use App\Core\Layout\ComponentRegistry;

class CustomComponentRegistration implements ComponentRegistration
{
    public function registerComponents(ComponentRegistry $registry): void
    {
        // Modules can register their own components without modifying core files
        $registry->register('customWidget', CustomWidget::class);
        $registry->register('customHeader', CustomHeader::class);
    }
}
