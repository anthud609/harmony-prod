<?php

// File: app/Core/Helpers/xss.php
// Global helper functions for XSS protection

if (! function_exists('e')) {
    /**
     * Escape HTML entities in the given string (shorthand)
     */
    function e($value): string
    {
        return \App\Core\Security\XssProtection::escape($value);
    }
}

if (! function_exists('attr')) {
    /**
     * Escape HTML attributes
     */
    function attr($value): string
    {
        return \App\Core\Security\XssProtection::attr($value);
    }
}

if (! function_exists('ejs')) {
    /**
     * Escape for JavaScript context
     */
    function ejs($value): string
    {
        return \App\Core\Security\XssProtection::js($value);
    }
}

if (! function_exists('eurl')) {
    /**
     * Escape URL parameters
     */
    function eurl($value): string
    {
        return \App\Core\Security\XssProtection::url($value);
    }
}

if (! function_exists('ecss')) {
    /**
     * Escape CSS values
     */
    function ecss($value): string
    {
        return \App\Core\Security\XssProtection::css($value);
    }
}

if (! function_exists('clean')) {
    /**
     * Clean HTML (allow specific tags)
     */
    function clean($html, array $allowedTags = []): string
    {
        return \App\Core\Security\XssProtection::clean($html, $allowedTags);
    }
}
