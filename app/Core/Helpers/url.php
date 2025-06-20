<?php

// File: app/Core/Helpers/url.php
// URL helper functions for subfolder installations

if (! function_exists('base_url')) {
    /**
     * Get the base URL of the application
     */
    function base_url($path = ''): string
    {
        // Get from environment or detect
        $baseUrl = config('app.url', '');

        if (empty($baseUrl)) {
            // Auto-detect base URL
            $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $basePath = dirname($scriptName);

            if ($basePath === '/' || $basePath === '\\') {
                $basePath = '';
            }

            $baseUrl = $protocol . '://' . $host . $basePath;
        }

        // Ensure no trailing slash on base URL
        $baseUrl = rtrim($baseUrl, '/');

        // Add the path
        if ($path) {
            // Ensure path starts with /
            if ($path[0] !== '/') {
                $path = '/' . $path;
            }

            return $baseUrl . $path;
        }

        return $baseUrl;
    }
}

if (! function_exists('asset')) {
    /**
     * Generate URL for assets (CSS, JS, images)
     */
    function asset($path): string
    {
        return base_url($path);
    }
}

if (! function_exists('route')) {
    /**
     * Generate URL for routes
     */
    function route($path): string
    {
        // For subfolder installations, we need to handle this properly
        return base_url($path);
    }
}

if (! function_exists('current_url')) {
    /**
     * Get the current URL
     */
    function current_url(): string
    {
        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return $protocol . '://' . $host . $uri;
    }
}
