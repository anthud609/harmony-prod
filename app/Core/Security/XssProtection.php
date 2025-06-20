<?php
// File: app/Core/Security/XssProtection.php
namespace App\Core\Security;

class XssProtection
{
    /**
     * Escape HTML entities in the given string
     */
    public static function escape($value, $flags = ENT_QUOTES | ENT_HTML5, $encoding = 'UTF-8'): string
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_array($value) || is_object($value)) {
            return '';
        }
        
        return htmlspecialchars((string)$value, $flags, $encoding);
    }
    
    /**
     * Escape HTML attributes
     */
    public static function attr($value): string
    {
        return self::escape($value, ENT_QUOTES | ENT_HTML5);
    }
    
    /**
     * Escape JavaScript/JSON content
     */
    public static function js($value): string
    {
        $escaped = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        return $escaped !== false ? $escaped : '""';
    }
    
    /**
     * Escape URL parameters
     */
    public static function url($value): string
    {
        return rawurlencode((string)$value);
    }
    
    /**
     * Escape CSS values
     */
    public static function css($value): string
    {
        // Remove any potentially dangerous characters
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)$value);
    }
    
    /**
     * Allow specific HTML tags (for rich content)
     */
    public static function clean($html, array $allowedTags = []): string
    {
        if (empty($allowedTags)) {
            // Default safe tags
            $allowedTags = ['b', 'i', 'em', 'strong', 'span', 'p', 'br'];
        }
        
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        return strip_tags((string)$html, $allowedTagsString);
    }
}