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
        return ConfigManager::getInstance()->env($key, $default);
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name.
     *
     * @return string
     */
    function app_name()
    {
        return config('app.name', 'Harmony HRMS');
    }
}

if (!function_exists('app_env')) {
    /**
     * Get the application environment.
     *
     * @return string
     */
    function app_env()
    {
        return config('app.env', 'production');
    }
}

if (!function_exists('is_production')) {
    /**
     * Check if application is in production.
     *
     * @return bool
     */
    function is_production()
    {
        return app_env() === 'production';
    }
}

if (!function_exists('is_local')) {
    /**
     * Check if application is in local environment.
     *
     * @return bool
     */
    function is_local()
    {
        return app_env() === 'local';
    }
}

if (!function_exists('is_debug')) {
    /**
     * Check if application is in debug mode.
     *
     * @return bool
     */
    function is_debug()
    {
        return config('app.debug', false) === true;
    }
}

if (!function_exists('feature')) {
    /**
     * Check if a feature is enabled.
     *
     * @param  string  $feature
     * @param  bool  $default
     * @return bool
     */
    function feature($feature, $default = false)
    {
        return config('features.' . $feature, $default) === true;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage path.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        $basePath = dirname(__DIR__, 3) . '/storage';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path($path = '')
    {
        $basePath = dirname(__DIR__, 3) . '/config';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path of the application.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        $basePath = dirname(__DIR__, 3);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}