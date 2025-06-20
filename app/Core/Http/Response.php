<?php
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

    public function getStatusCode(): int
    {
        return $this->statusCode;
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

    /**
     * Create a redirect response
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->setStatusCode($status);
        $this->setHeader('Location', $url);
        $this->setContent('');
        return $this;
    }

    /**
     * Create a JSON response
     */
    public function json($data, int $status = 200): self
    {
        $this->setStatusCode($status);
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        return $this;
    }

    /**
     * Actually send status, headers, and body to the client.
     */
    public function send(): void
    {
        // Check if headers already sent
        if (headers_sent()) {
            return;
        }

        // 1) Status code
        http_response_code($this->statusCode);

        // 2) Headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // 3) Body (only if not a redirect)
        if ($this->statusCode < 300 || $this->statusCode >= 400) {
            echo $this->content;
        }
    }
}