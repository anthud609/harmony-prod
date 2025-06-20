<?php
// File: app/Core/Security/SessionManager.php
namespace App\Core\Security;

class SessionManager
{
    private const SESSION_LIFETIME = 360; // 6 minutes total (5 min activity + 1 min warning)
    private const SESSION_NAME = 'HARMONY_SESSID';
    private const FINGERPRINT_KEY = '_session_fingerprint';
    private const LAST_ACTIVITY_KEY = '_last_activity';
    private const CREATED_TIME_KEY = '_created_time';
    
    /**
     * Initialize secure session with proper configuration
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configure session security settings
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        
        // IMPORTANT: Set gc_maxlifetime to be longer than our activity timeout
        // This ensures PHP doesn't garbage collect our session prematurely
        ini_set('session.gc_maxlifetime', '3600'); // 1 hour - much longer than our timeout
        
        // Use secure cookies if HTTPS
        if (self::isHttps()) {
            ini_set('session.cookie_secure', '1');
        }
        
        // Set custom session name
        session_name(self::SESSION_NAME);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires on browser close)
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Start the session
        session_start();
        
        // Validate session integrity
        self::validateSession();
        
        // Update activity timestamp
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
    }
    
    /**
     * Regenerate session ID (prevent fixation attacks)
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Regenerate session ID
            session_regenerate_id($deleteOld);
            
            // Update fingerprint with new session ID
            $_SESSION[self::FINGERPRINT_KEY] = self::generateFingerprint();
            $_SESSION[self::CREATED_TIME_KEY] = time();
        }
    }
    
    /**
     * Validate session integrity and timeout
     */
    private static function validateSession(): void
    {
        // Check session timeout
        if (isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            $elapsed = time() - $_SESSION[self::LAST_ACTIVITY_KEY];
            if ($elapsed > self::SESSION_LIFETIME) {
                self::destroy();
                throw new \Exception('Session expired');
            }
        }
        
        // Validate session fingerprint
        if (isset($_SESSION[self::FINGERPRINT_KEY])) {
            $currentFingerprint = self::generateFingerprint();
            if (!hash_equals($_SESSION[self::FINGERPRINT_KEY], $currentFingerprint)) {
                self::destroy();
                throw new \Exception('Session fingerprint mismatch');
            }
        } else {
            // First time - set fingerprint
            $_SESSION[self::FINGERPRINT_KEY] = self::generateFingerprint();
            $_SESSION[self::CREATED_TIME_KEY] = time();
        }
        
        // Regenerate session ID periodically (every 30 minutes)
        if (isset($_SESSION[self::CREATED_TIME_KEY])) {
            if (time() - $_SESSION[self::CREATED_TIME_KEY] > 1800) {
                self::regenerate();
            }
        }
    }
    
    /**
     * Generate session fingerprint
     */
    private static function generateFingerprint(): string
    {
        $fingerprint = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            // Don't use IP as it can change for mobile users
        ];
        
        return hash('sha256', implode('|', $fingerprint));
    }
    
    /**
     * Set secure session data
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session data
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session has key
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session securely
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear session data
            $_SESSION = [];
            
            // Delete session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            // Destroy session
            session_destroy();
        }
    }
    
    /**
     * Check if connection is HTTPS
     */
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get remaining session lifetime in seconds
     */
    public static function getRemainingLifetime(): int
    {
        if (!isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return 0;
        }
        
        $elapsed = time() - $_SESSION[self::LAST_ACTIVITY_KEY];
        $remaining = self::SESSION_LIFETIME - $elapsed;
        
        return max(0, $remaining);
    }
    
    /**
     * Extend session lifetime
     */
    public static function extend(): void
    {
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user');
    }
    
    /**
     * Get current user
     */
    public static function getUser(): ?array
    {
        return self::get('user');
    }
}