<?php
// File: app/Core/Security/XssProtection.php

namespace App\Core\Security;

/**
 * @deprecated Use helper functions directly (e(), attr(), ejs(), etc.)
 */
class XssProtection
{
    /**
     * @deprecated Use e() helper function instead
     */
    public static function escape($value, $flags = null, $encoding = 'UTF-8'): string
    {
        trigger_error('e(XssProtection::e() is deprecated. Use e() helper instead.', E_USER_DEPRECATED);
        return e($value);
    }

    /**
     * @deprecated Use attr() helper function instead
     */
    public static function attr($value): string
    {
        trigger_error('XssProtection::attr() is deprecated. Use attr() helper instead.', E_USER_DEPRECATED);
        return attr($value);
    }

    /**
     * @deprecated Use ejs() helper function instead
     */
    public static function js($value): string
    {
        trigger_error('XssProtection::js() is deprecated. Use ejs() helper instead.', E_USER_DEPRECATED);
        return ejs($value);
    }

    /**
     * @deprecated Use eurl() helper function instead
     */
    public static function url($value): string
    {
        trigger_error('XssProtection::url() is deprecated. Use eurl() helper instead.', E_USER_DEPRECATED);
        return eurl($value);
    }

    /**
     * @deprecated Use ecss() helper function instead
     */
    public static function css($value): string
    {
        trigger_error('XssProtection::css() is deprecated. Use ecss() helper instead.', E_USER_DEPRECATED);
        return ecss($value);
    }

    /**
     * @deprecated DO NOT USE - This allows HTML which is dangerous
     */
    public static function clean($html, array $allowedTags = []): string
    {
        trigger_error('XssProtection::clean() is deprecated and unsafe. Use e() instead.', E_USER_DEPRECATED);
        return e($html);
    }
}