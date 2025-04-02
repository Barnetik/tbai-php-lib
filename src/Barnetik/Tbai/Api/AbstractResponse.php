<?php

namespace Barnetik\Tbai\Api;

abstract class AbstractResponse implements ResponseInterface
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

    public function headers(): array
    {
        return $this->headers;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return ['status' => $this->status,'headers' => $this->headers(), 'content' => $this->content()];
    }

    public function saveResponseContent(string $path): void
    {
        file_put_contents($path, $this->content());
    }

    public function saveFullResponse(string $path): void
    {
        file_put_contents($path, json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE));
    }

    abstract public function isDelivered(): bool;
    abstract public function isCorrect(): bool;
    abstract public function mainErrorMessage(): string;
    abstract public function errorDataRegistry(): array;
    abstract public function hasErrorData(): bool;
}
