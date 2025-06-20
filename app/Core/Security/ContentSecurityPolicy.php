<?php

// File: app/Core/Security/ContentSecurityPolicy.php

namespace App\Core\Security;

class ContentSecurityPolicy
{
    private array $directives = [];
    private ?string $nonce = null;   // ← declare it here

    public function __construct()
    {
        // Set default secure policies
        $this->directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", 'https://cdn.tailwindcss.com', 'https://cdnjs.cloudflare.com'],
            'style-src' => ["'self'", "'unsafe-inline'", 'https://cdnjs.cloudflare.com'],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'", 'https://cdnjs.cloudflare.com'],
            'connect-src' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"],
        ];
    }

    public function addDirective(string $directive, array $sources): self
    {
        $this->directives[$directive] = array_merge(
            $this->directives[$directive] ?? [],
            $sources
        );

        return $this;
    }

    public function getNonce(): string
    {
        if (! isset($this->nonce)) {
            $this->nonce = base64_encode(random_bytes(16));
        }

        return $this->nonce;
    }

    public function getHeader(): string
    {
        $policies = [];
        foreach ($this->directives as $directive => $sources) {
            $policies[] = $directive . ' ' . implode(' ', $sources);
        }

        return implode('; ', $policies);
    }

    public function send(): void
    {
        header('Content-Security-Policy: ' . $this->getHeader());
    }
}
