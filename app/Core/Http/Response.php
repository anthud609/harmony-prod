<?php


// File: app/Core/Http/Response.php
namespace App\Core\Http;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';
    
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        echo $this->content;
    }
    
    public function redirect(string $url, int $status = 302): self
    {
        $this->setStatusCode($status);
        $this->setHeader('Location', $url);
        return $this;
    }
    
    public function json(array $data, int $status = 200): self
    {
        $this->setStatusCode($status);
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }
}