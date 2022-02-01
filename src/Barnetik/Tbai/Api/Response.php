<?php

namespace Barnetik\Tbai\Api;

abstract class Response
{
    protected string $status;
    protected string $content;
    protected array $headers;

    public function __construct(string $status, array $headers, string $content)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function header(string $key): string
    {
        return $this->headers[$key];
    }

    public function saveResponseContent(string $path): void
    {
        file_put_contents($path, $this->content);
    }

    abstract public function isCorrect(): bool;
    abstract public function mainErrorMessage(): string;
    abstract public function content(): string;

}
