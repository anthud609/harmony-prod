<?php

// File: app/Core/Security/SessionManager.php (Updated with configuration)

namespace App\Core\Security;

use App\Core\Traits\LoggerTrait;

class SessionManager
{
    use LoggerTrait;

    private int $sessionLifetime;
    private string $sessionName;
    private const FINGERPRINT_KEY = '_session_fingerprint';
    private const LAST_ACTIVITY_KEY = '_last_activity';
    private const CREATED_TIME_KEY = '_created_time';

public function __construct()
{
    // Load configuration - lifetime is in MINUTES in config
    $lifetimeMinutes = config('session.lifetime', 120); // Default 2 hours
    $this->sessionLifetime = $lifetimeMinutes * 60; // Convert to seconds
    $this->sessionName = config('session.cookie', 'HARMONY_SESSID');
}

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
            if ($updateActivity && ! $this->isSessionStatusCheck()) {
                $_SESSION[self::LAST_ACTIVITY_KEY] = time();
                $this->logDebug('Activity timestamp updated', [
                    'path' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                    'newActivity' => $_SESSION[self::LAST_ACTIVITY_KEY],
                ]);
            }

            return;
        }

        // Configure session from environment/config
        $this->configureSession();

        // Set custom session name
        session_name($this->sessionName);

        // Set session cookie parameters from config
        session_set_cookie_params([
            'lifetime' => config('session.expire_on_close') ? 0 : config('session.lifetime') * 60,
            'path' => config('session.path', '/'),
            'domain' => config('session.domain', ''),
            'secure' => config('session.secure', $this->isHttps()),
            'httponly' => config('session.http_only', true),
            'samesite' => config('session.same_site', 'lax'),
        ]);

        // Start the session
        session_start();

        $this->logDebug('Session started', [
            'sessionId' => session_id(),
            'sessionName' => session_name(),
            'lifetime' => $this->sessionLifetime,
        ]);

        // Validate session integrity
        $this->validateSession();

        // Update activity timestamp only if requested and not a status check
        if ($updateActivity && ! $this->isSessionStatusCheck()) {
            $_SESSION[self::LAST_ACTIVITY_KEY] = time();
            $this->logDebug('Initial activity timestamp set', [
                'timestamp' => $_SESSION[self::LAST_ACTIVITY_KEY],
            ]);
        }
    }

    /**
     * Configure PHP session settings from config
     */
    private function configureSession(): void
    {
        // Basic settings
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', config('session.same_site', 'Lax'));

        // Set gc_maxlifetime based on config
        $gcMaxLifetime = max(
            config('session.lifetime', 360) * 2, // Double the session lifetime
            3600 // Minimum 1 hour
        );
        ini_set('session.gc_maxlifetime', (string)$gcMaxLifetime);

        // Use secure cookies if HTTPS
        if ($this->isHttps() || config('session.secure', false)) {
            ini_set('session.cookie_secure', '1');
        }

        // Set session save path if configured
        if (config('session.driver') === 'file') {
            $savePath = config('session.files', storage_path('sessions'));
            if (! is_dir($savePath)) {
                mkdir($savePath, 0o755, true);
            }
            session_save_path($savePath);
        }

        // Configure session garbage collection
        $lottery = config('session.lottery', [2, 100]);
        ini_set('session.gc_probability', (string)$lottery[0]);
        ini_set('session.gc_divisor', (string)$lottery[1]);
    }

private function isSessionStatusCheck(): bool
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    // Check for the actual API endpoint path
    $isStatusCheck = strpos($requestUri, '/api/session-status') !== false ||
                     strpos($requestUri, 'session-status') !== false;

    if ($isStatusCheck) {
        $this->logDebug('Session status check detected - NOT updating activity', [
            'uri' => $requestUri
        ]);
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
                'oldSessionId' => substr($oldSessionId, 0, 8) . '...',
                'newSessionId' => substr(session_id(), 0, 8) . '...',
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
            if ($elapsed > $this->sessionLifetime) {
                $this->logInfo('Session expired due to timeout', [
                    'elapsed' => $elapsed,
                    'lifetime' => $this->sessionLifetime,
                    'lastActivity' => date('Y-m-d H:i:s', $_SESSION[self::LAST_ACTIVITY_KEY]),
                ]);
                $this->destroy();

                throw new \Exception('Session expired');
            }
        }

        // Validate session fingerprint
        if (isset($_SESSION[self::FINGERPRINT_KEY])) {
            $currentFingerprint = $this->generateFingerprint();
            $storedFingerprint = $_SESSION[self::FINGERPRINT_KEY];

            if (! hash_equals($storedFingerprint, $currentFingerprint)) {
                $this->logWarning('Session fingerprint mismatch', [
                    'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'environment' => app_env(),
                ]);

                // Only enforce in production
                if (is_production() && ! config('session.disable_fingerprint_check', false)) {
                    $this->destroy();

                    throw new \Exception('Session fingerprint mismatch');
                }
            }
        } else {
            // First time - set fingerprint
            $_SESSION[self::FINGERPRINT_KEY] = $this->generateFingerprint();
            $_SESSION[self::CREATED_TIME_KEY] = time();
        }

        // Regenerate session ID periodically
        $regenerateAfter = config('session.regenerate_after', 1800); // 30 minutes default
        if (isset($_SESSION[self::CREATED_TIME_KEY])) {
            $age = time() - $_SESSION[self::CREATED_TIME_KEY];
            if ($age > $regenerateAfter) {
                $this->logDebug('Session ID regeneration due to age', ['age' => $age]);
                $this->regenerate();
            }
        }
    }

    /**
     * Generate session fingerprint
     */
    private function generateFingerprint(): string
    {
        // Get User-Agent and normalize it
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Only use major browser version in production
        if (is_production()) {
            $userAgent = $this->normalizeUserAgent($userAgent);
        }

        $fingerprint = [
            $userAgent,
            // Add more components if needed, but be careful
            // as too many components can cause false positives
        ];

        $fingerprintString = implode('|', $fingerprint);

        return hash('sha256', $fingerprintString);
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
     * Check if connection is HTTPS
     */
    private function isHttps(): bool
    {
        return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || config('app.force_https', false);
    }

    /**
     * Get remaining session lifetime in seconds
     */
    public function getRemainingLifetime(): int
    {
        if (! isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return 0;
        }

        $elapsed = time() - $_SESSION[self::LAST_ACTIVITY_KEY];
        $remaining = $this->sessionLifetime - $elapsed;

        $this->logDebug('Calculating remaining lifetime', [
            'lastActivity' => $_SESSION[self::LAST_ACTIVITY_KEY],
            'currentTime' => time(),
            'elapsed' => $elapsed,
            'remaining' => $remaining,
            'configuredLifetime' => $this->sessionLifetime,
        ]);

        return max(0, $remaining);
    }

    // ... rest of the methods remain the same ...

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

            $this->logInfo('Session destroyed', [
                'sessionId' => substr($sessionId, 0, 8) . '...',
                'environment' => app_env(),
            ]);
        }
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
            'newLifetime' => $this->getRemainingLifetime(),
            'environment' => app_env(),
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
