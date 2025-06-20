<?php
// File: app/Core/Helpers/xss.php

if (!function_exists('e')) {
    /**
     * Escape HTML entities in the given string (shorthand)
     * This is THE standard way to escape output in views
     */
    function e($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_array($value) || is_object($value)) {
            return '';
        }

        // FIXED: Call htmlspecialchars, not e() recursively
        return htmlspecialchars(
            (string)$value, 
            ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE,
            'UTF-8',
            false // Don't double-encode
        );
    }
}

// Keep other helpers but ensure they're used appropriately
if (!function_exists('attr')) {
    /**
     * Escape HTML attributes - same as e() but named for clarity
     */
    function attr($value): string
    {
        return e($value);
    }
}

if (!function_exists('ejs')) {
    /**
     * Escape for JavaScript context
     * ONLY use this when outputting PHP values into JavaScript
     */
    function ejs($value): string
    {
        $escaped = json_encode($value, 
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );
        
        return $escaped !== false ? $escaped : '""';
    }
}

if (!function_exists('eurl')) {
    /**
     * Escape URL parameters
     */
    function eurl($value): string
    {
        return rawurlencode((string)$value);
    }
}

if (!function_exists('ecss')) {
    /**
     * Escape CSS values - only allows alphanumeric, dash, and underscore
     */
    function ecss($value): string
    {
        // Be very restrictive with CSS values
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)$value);
    }
}

if (!function_exists('clean')) {
    /**
     * DEPRECATED - DO NOT USE
     * This function is dangerous as it allows HTML
     * @deprecated Use e() instead
     */
    function clean($html, array $allowedTags = []): string
    {
        trigger_error('clean() is deprecated and unsafe. Use e() instead.', E_USER_DEPRECATED);
        return e($html); // Force safe escaping
    }
}