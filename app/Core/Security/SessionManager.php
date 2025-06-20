<?php
// File: app/Core/Security/SessionManager.php
namespace App\Core\Security;

use App\Core\Traits\LoggerTrait;

class SessionManager
{
    use LoggerTrait;
    
    private const SESSION_LIFETIME = 360; // 6 minutes total (5 min activity + 1 min warning)
    private const SESSION_NAME = 'HARMONY_SESSID';
    private const FINGERPRINT_KEY = '_session_fingerprint';
    private const LAST_ACTIVITY_KEY = '_last_activity';
    private const CREATED_TIME_KEY = '_created_time';
    
    /**
     * Initialize secure session with proper configuration
     * @param bool $updateActivity Whether to update the last activity timestamp
     */
    public function init(bool $updateActivity = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Session already active, just validate and maybe update activity
            $this->validateSession();
            
            // Only update activity if requested AND not a session status check
            if ($updateActivity && !$this->isSessionStatusCheck()) {
                $_SESSION[self::LAST_ACTIVITY_KEY] = time();
                $this->logDebug('Activity timestamp updated', [
                    'path' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                    'newActivity' => $_SESSION[self::LAST_ACTIVITY_KEY]
                ]);
            }
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
        if ($this->isHttps()) {
            ini_set('session.cookie_secure', '1');
        }
        
        // Set custom session name
        session_name(self::SESSION_NAME);
        
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires on browser close)
            'path' => '/',
            'domain' => '',
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        // Start the session
        session_start();
        
        $this->logDebug('Session started', [
            'sessionId' => session_id(),
            'sessionName' => session_name()
        ]);
        
        // Validate session integrity
        $this->validateSession();
        
        // Update activity timestamp only if requested and not a status check
        if ($updateActivity && !$this->isSessionStatusCheck()) {
            $_SESSION[self::LAST_ACTIVITY_KEY] = time();
            $this->logDebug('Initial activity timestamp set', [
                'timestamp' => $_SESSION[self::LAST_ACTIVITY_KEY]
            ]);
        }
    }
    
    /**
     * Check if current request is a session status check
     */
    private function isSessionStatusCheck(): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $isStatusCheck = strpos($requestUri, '/api/session-status') !== false;
        
        if ($isStatusCheck) {
            $this->logDebug('Session status check detected - NOT updating activity');
        }
        
        return $isStatusCheck;
    }
    
    /**
     * Regenerate session ID (prevent fixation attacks)
     */
    public function regenerate(bool $deleteOld = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $oldSessionId = session_id();
            
            // Regenerate session ID
            session_regenerate_id($deleteOld);
            
            // Update fingerprint with new session ID
            $_SESSION[self::FINGERPRINT_KEY] = $this->generateFingerprint();
            $_SESSION[self::CREATED_TIME_KEY] = time();
            
            $this->logDebug('Session ID regenerated', [
                'oldSessionId' => $oldSessionId,
                'newSessionId' => session_id()
            ]);
        }
    }
    
    /**
     * Validate session integrity and timeout
     */
    private function validateSession(): void
    {
        // Check session timeout
        if (isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            $elapsed = time() - $_SESSION[self::LAST_ACTIVITY_KEY];
            if ($elapsed > self::SESSION_LIFETIME) {
                $this->logInfo('Session expired due to timeout', [
                    'elapsed' => $elapsed,
                    'lifetime' => self::SESSION_LIFETIME,
                    'lastActivity' => date('Y-m-d H:i:s', $_SESSION[self::LAST_ACTIVITY_KEY])
                ]);
                $this->destroy();
                throw new \Exception('Session expired');
            }
        }
        
        // Validate session fingerprint
        if (isset($_SESSION[self::FINGERPRINT_KEY])) {
            $currentFingerprint = $this->generateFingerprint();
            $storedFingerprint = $_SESSION[self::FINGERPRINT_KEY];
            
            if (!hash_equals($storedFingerprint, $currentFingerprint)) {
                $this->logWarning('Session fingerprint mismatch', [
                    'stored' => substr($storedFingerprint, 0, 8) . '...',
                    'current' => substr($currentFingerprint, 0, 8) . '...',
                    'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
                
                // In development, log more details
                if ($_ENV['APP_DEBUG'] ?? false) {
                    $this->logDebug('Fingerprint components', [
                        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'acceptLanguage' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
                        'acceptEncoding' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''
                    ]);
                }
                
                $this->destroy();
                throw new \Exception('Session fingerprint mismatch');
            }
        } else {
            // First time - set fingerprint
            $_SESSION[self::FINGERPRINT_KEY] = $this->generateFingerprint();
            $_SESSION[self::CREATED_TIME_KEY] = time();
            
            $this->logDebug('Session fingerprint created', [
                'fingerprint' => substr($_SESSION[self::FINGERPRINT_KEY], 0, 8) . '...'
            ]);
        }
        
        // Regenerate session ID periodically (every 30 minutes)
        if (isset($_SESSION[self::CREATED_TIME_KEY])) {
            $age = time() - $_SESSION[self::CREATED_TIME_KEY];
            if ($age > 1800) {
                $this->logDebug('Session ID regeneration due to age', ['age' => $age]);
                $this->regenerate();
            }
        }
    }
    
    /**
     * Generate session fingerprint
     * Note: We're using a minimal fingerprint to avoid false positives
     */
    private function generateFingerprint(): string
    {
        // Get User-Agent and normalize it
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Extract major browser version to handle auto-updates
        $userAgent = $this->normalizeUserAgent($userAgent);
        
        $fingerprint = [
            $userAgent,
            // We can add more components if needed, but be careful
            // as too many components can cause false positives
        ];
        
        $fingerprintString = implode('|', $fingerprint);
        $hash = hash('sha256', $fingerprintString);
        
        $this->logDebug('Fingerprint generated', [
            'components' => count($fingerprint),
            'hash' => substr($hash, 0, 8) . '...'
        ]);
        
        return $hash;
    }
    
    /**
     * Normalize user agent to extract major version only
     */
    private function normalizeUserAgent(string $userAgent): string
    {
        // Extract major browser information only
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            return 'Chrome/' . $matches[1];
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            return 'Firefox/' . $matches[1];
        } elseif (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            return 'Safari/' . $matches[1];
        } elseif (preg_match('/Edge\/(\d+)/', $userAgent, $matches)) {
            return 'Edge/' . $matches[1];
        }
        
        // For other browsers, just use the full agent
        return $userAgent;
    }
    
    /**
     * Set secure session data
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
        $this->logDebug('Session data set', ['key' => $key]);
    }
    
    /**
     * Get session data
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session has key
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session data
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
        $this->logDebug('Session data removed', ['key' => $key]);
    }
    
    /**
     * Destroy session securely
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            
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
            
            $this->logInfo('Session destroyed', ['sessionId' => $sessionId]);
        }
    }
    
    /**
     * Check if connection is HTTPS
     */
    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || $_SERVER['SERVER_PORT'] == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get remaining session lifetime in seconds
     */
    public function getRemainingLifetime(): int
    {
        if (!isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return 0;
        }
        
        $elapsed = time() - $_SESSION[self::LAST_ACTIVITY_KEY];
        $remaining = self::SESSION_LIFETIME - $elapsed;
        
        $this->logDebug('Calculating remaining lifetime', [
            'lastActivity' => $_SESSION[self::LAST_ACTIVITY_KEY],
            'currentTime' => time(),
            'elapsed' => $elapsed,
            'remaining' => $remaining
        ]);
        
        return max(0, $remaining);
    }
    
    /**
     * Extend session lifetime
     */
    public function extend(): void
    {
        $oldActivity = $_SESSION[self::LAST_ACTIVITY_KEY] ?? null;
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        
        $this->logInfo('Session activity extended', [
            'oldActivity' => $oldActivity ? date('Y-m-d H:i:s', $oldActivity) : 'null',
            'newActivity' => date('Y-m-d H:i:s', $_SESSION[self::LAST_ACTIVITY_KEY]),
            'newLifetime' => $this->getRemainingLifetime()
        ]);
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return $this->has('user');
    }
    
    /**
     * Get current user
     */
    public function getUser(): ?array
    {
        return $this->get('user');
    }
}