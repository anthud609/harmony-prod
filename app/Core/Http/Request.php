<?php
// File: app/Core/Http/Request.php
namespace App\Core\Http;

class Request
{
    private array $query;
    private array $post;
    private array $server;
    private array $cookies;
    private array $files;
    private array $attributes = [];
    
    public function __construct(
        array $query = [],
        array $post = [],
        array $server = [],
        array $cookies = [],
        array $files = []
    ) {
        $this->query = $query;
        $this->post = $post;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
    }
    
    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }
    
    public function getQuery(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }
    
    public function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }
    
    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }
    
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }
    
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
}