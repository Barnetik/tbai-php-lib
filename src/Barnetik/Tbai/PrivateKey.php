<?php

namespace Barnetik\Tbai;

class PrivateKey
{
    const TYPE_P12 = 'P12';
    const TYPE_PEM = 'PEM';
    private string $type;
    private array $files;

    private function __construct()
    {
    }

    public static function p12(string $filePath): self
    {
        $privateKey = new PrivateKey();
        $privateKey->type = self::TYPE_P12;
        $privateKey->files = [
            'pfx' => $filePath
        ];
        return $privateKey;
    }

    public static function pem(string $certPath, string $keyPath): self
    {
        $privateKey = new PrivateKey();
        $privateKey->type = self::TYPE_PEM;
        $privateKey->files = [
            'cert' => $certPath,
            'key' => $keyPath,
        ];
        return $privateKey;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function certPath(): ?string
    {
        if ($this->type === self::TYPE_P12) {
            return $this->files['pfx'];
        }

        if ($this->type === self::TYPE_PEM) {
            return $this->files['cert'];
        }

        return null;
    }

    public function keyPath(): ?string
    {
        if ($this->type === self::TYPE_P12) {
            return $this->files['pfx'];
        }

        if ($this->type === self::TYPE_PEM) {
            return $this->files['key'];
        }

        return null;
    }
}
