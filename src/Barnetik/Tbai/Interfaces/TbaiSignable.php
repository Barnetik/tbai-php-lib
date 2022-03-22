<?php

namespace Barnetik\Tbai\Interfaces;

interface TbaiSignable
{
    public function sign(string $pfxFilePath, string $password, string $signedFilePath): void;
    public function signed(): string;
    public function isSigned(): bool;

    public function base64Signed(): string;

    public function signatureValue(): string;
    public function shortSignatureValue(): string;

    public function moveSignedXmlTo(string $newPath): void;
    public function signedXmlPath(): string;
}
