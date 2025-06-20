<?php

// File: app/Core/Security/CsrfProtection.php

namespace App\Core\Security;

use Exception;

class CsrfProtection
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAME = 'csrf_token';
    private const HEADER_NAME = 'X-CSRF-Token';
    private const MAX_TOKENS = 5; // Support multiple valid tokens for better UX
    private const TOKEN_LIFETIME = 3600; // 1 hour

    private bool $initialized = false;

    /**
     * Initialize CSRF protection
     */
    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        if (! isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }

        // Clean expired tokens
        $this->cleanExpiredTokens();

        $this->initialized = true;
    }

    /**
     * Generate a new CSRF token
     */
    public function generateToken(): string
    {
        $this->init();

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $timestamp = time();

        // Store token with timestamp
        $_SESSION['csrf_tokens'][] = [
            'token' => $token,
            'created' => $timestamp,
        ];

        // Keep only the most recent tokens
        if (count($_SESSION['csrf_tokens']) > self::MAX_TOKENS) {
            array_shift($_SESSION['csrf_tokens']);
        }

        return $token;
    }

    /**
     * Get the current CSRF token (generates if none exists)
     */
    public function getToken(): string
    {
        $this->init();

        // Return the most recent valid token or generate a new one
        $tokens = $_SESSION['csrf_tokens'] ?? [];
        if (! empty($tokens)) {
            $latestToken = end($tokens);
            if ($this->isTokenValid($latestToken)) {
                return $latestToken['token'];
            }
        }

        return $this->generateToken();
    }

    /**
     * Verify CSRF token from request
     */
    public function verifyToken(string $token = null): bool
    {
        $this->init();

        // Get token from various sources
        if ($token === null) {
            $token = $this->getTokenFromRequest();
        }

        if (empty($token)) {
            return false;
        }

        // Check against all valid tokens
        foreach ($_SESSION['csrf_tokens'] ?? [] as $storedToken) {
            if (
                $this->isTokenValid($storedToken) &&
                hash_equals($storedToken['token'], $token)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify request or throw exception
     */
    public function verifyRequest(): void
    {
        // Skip CSRF check for safe methods
        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        if (! $this->verifyToken()) {
            throw new CsrfException('Invalid or missing CSRF token');
        }
    }

    /**
     * Get HTML input field with CSRF token
     */
    public function getHiddenField(): string
    {
        $token = $this->getToken();

        return sprintf(
            '<input type="hidden" name="%s" value="%s" />',
            htmlspecialchars(self::TOKEN_NAME),
            htmlspecialchars($token)
        );
    }

    /**
     * Get meta tag for AJAX requests
     */
    public function getMetaTag(): string
    {
        $token = $this->getToken();

        return sprintf(
            '<meta name="%s" content="%s" />',
            htmlspecialchars(self::TOKEN_NAME),
            htmlspecialchars($token)
        );
    }

    /**
     * Get token from request (POST body, header, or query)
     */
    private function getTokenFromRequest(): ?string
    {
        // Check POST data
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }

        // Check custom header
        $headerName = 'HTTP_' . str_replace('-', '_', strtoupper(self::HEADER_NAME));
        if (isset($_SERVER[$headerName])) {
            return $_SERVER[$headerName];
        }

        // Check standard CSRF header variations
        foreach (['HTTP_X_CSRF_TOKEN', 'HTTP_X_XSRF_TOKEN'] as $header) {
            if (isset($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }

        return null;
    }

    /**
     * Clean expired tokens
     */
    private function cleanExpiredTokens(): void
    {
        if (! isset($_SESSION['csrf_tokens'])) {
            return;
        }

        $_SESSION['csrf_tokens'] = array_values(array_filter(
            $_SESSION['csrf_tokens'],
            [$this, 'isTokenValid']
        ));
    }

    /**
     * Check if token is still valid
     */
    private function isTokenValid(array $tokenData): bool
    {
        return isset($tokenData['created']) &&
               (time() - $tokenData['created']) < self::TOKEN_LIFETIME;
    }
}
