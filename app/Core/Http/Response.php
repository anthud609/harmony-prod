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
     * Actually send status, headers, and body to the client.
     */
    public function send(): void
    {
        // 1) Status code
        http_response_code($this->statusCode);

        // 2) Headers
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // 3) Body
        echo $this->content;
    }
}
