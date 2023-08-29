<?php

namespace Barnetik\Tbai\Api;

interface ResponseInterface
{
    public function __construct(string $status, array $headers, string $content);
    public function status(): string;
    public function header(string $key): string;
    public function content(): string;
    public function isDelivered(): bool;
    public function isCorrect(): bool;
    public function mainErrorMessage(): string;
    public function saveResponseContent(string $path): void;
    public function errorDataRegistry(): array;
    public function hasErrorData(): bool;
}
