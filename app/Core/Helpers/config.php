<?php
// File: app/Core/Helpers/config.php
// Global helper functions for configuration

use App\Core\Config\ConfigManager;

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|ConfigManager
     */
    function config($key = null, $default = null)
    {
        $config = ConfigManager::getInstance();
        
        // Initialize config if not loaded
        $config->initialize();
        
        if (is_null($key)) {
            return $config;
        }
        
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $config->set($k, $v);
            }
            return null;
        }
        
        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convert string booleans
        if (is_string($value)) {
            $valueLower = strtolower($value);
            if ($valueLower === 'true' || $valueLower === '(true)') {
                return true;
            }
            if ($valueLower === 'false' || $valueLower === '(false)') {
                return false;
            }
            if ($valueLower === 'null' || $valueLower === '(null)') {
                return null;
            }
        }
        
        return $value;
    }
}

// Rest of the helper functions remain the same...
if (!function_exists('app_name')) {
    function app_name()
    {
        return config('app.name', 'Harmony HRMS');
    }
}

if (!function_exists('app_env')) {
    function app_env()
    {
        return config('app.env', 'production');
    }
}

if (!function_exists('is_production')) {
    function is_production()
    {
        return app_env() === 'production';
    }
}

if (!function_exists('is_local')) {
    function is_local()
    {
        return app_env() === 'local';
    }
}

if (!function_exists('is_debug')) {
    function is_debug()
    {
        return config('app.debug', false) === true;
    }
}

if (!function_exists('feature')) {
    function feature($feature, $default = false)
    {
        return config('features.' . $feature, $default) === true;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        $basePath = dirname(__DIR__, 3) . '/storage';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        $basePath = dirname(__DIR__, 3) . '/config';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        $basePath = dirname(__DIR__, 3);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}