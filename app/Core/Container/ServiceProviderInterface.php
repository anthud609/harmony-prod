<?php
// File: app/Core/Container/ServiceProviderInterface.php
namespace App\Core\Container;

interface ServiceProviderInterface
{
    /**
     * Get service definitions for the DI container
     */
    public function getDefinitions(): array;
}