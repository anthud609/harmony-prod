<?php

// File: app/Core/Helpers/csrf.php
// Global helper functions for templates

use App\Core\Container\ContainerFactory;
use App\Core\Security\CsrfProtection;

if (! function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden field
     */
    function csrf_field(): string
    {
        $container = ContainerFactory::getInstance();
        $csrfProtection = $container->get(CsrfProtection::class);

        return $csrfProtection->getHiddenField();
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get current CSRF token
     */
    function csrf_token(): string
    {
        $container = ContainerFactory::getInstance();
        $csrfProtection = $container->get(CsrfProtection::class);

        return $csrfProtection->getToken();
    }
}

if (! function_exists('csrf_meta')) {
    /**
     * Generate CSRF meta tag
     */
    function csrf_meta(): string
    {
        $container = ContainerFactory::getInstance();
        $csrfProtection = $container->get(CsrfProtection::class);

        return $csrfProtection->getMetaTag();
    }
}
