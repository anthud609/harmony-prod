<?php
// File: app/Core/Helpers/csrf.php
// Global helper functions for templates

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden field
     */
    function csrf_field(): string
    {
        return \App\Core\Security\CsrfProtection::getHiddenField();
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get current CSRF token
     */
    function csrf_token(): string
    {
        return \App\Core\Security\CsrfProtection::getToken();
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate CSRF meta tag
     */
    function csrf_meta(): string
    {
        return \App\Core\Security\CsrfProtection::getMetaTag();
    }
}