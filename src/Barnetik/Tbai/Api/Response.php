<?php

namespace Barnetik\Tbai\Api;

class Response
{
    private string $content;
    private array $headers;

    public function __construct(array $headers, string $content)
    {
        $this->headers = $headers;
        $this->content = $content;
    }

    public function isCorrect(): bool
    {
        return $this->headers['eus-bizkaia-n3-tipo-respuesta'] !== 'Incorrecto';
    }

    public function headerErrorMessage(): string
    {
        return $this->headers['eus-bizkaia-n3-mensaje-respuesta'];
    }

    public function content(): string
    {
        return gzdecode($this->content);
    }

    public function header(string $key): string
    {
        return $this->headers[$key];
    }

    public function saveResponseContent(string $path): void
    {
        file_put_contents($path, $this->content);
    }
}
