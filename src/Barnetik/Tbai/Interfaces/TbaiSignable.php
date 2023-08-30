<?php

namespace Barnetik\Tbai\Interfaces;

use Barnetik\Tbai\PrivateKey;

interface TbaiSignable
{
    public function sign(PrivateKey $privateKey, string $password, string $signedFileStoragePath): void;
    public function signed(): string;
    public function isSigned(): bool;

    public function base64Signed(): string;

    public function signatureValue(): string;
    public function shortSignatureValue(): string;

    public function moveSignedXmlTo(string $newPath): void;
    public function signedXmlPath(): string;
}
