<?php
// File: app/Core/Layout/ComponentRegistration.php (New)
namespace App\Core\Layout;

/**
 * Example of how modules can register their own components
 * This fixes the Open/Closed violation
 */
interface ComponentRegistration
{
    /**
     * Register components with the registry
     */
    public function registerComponents(ComponentRegistry $registry): void;
}