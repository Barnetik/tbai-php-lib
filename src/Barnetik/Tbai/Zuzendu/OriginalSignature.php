<?php

namespace Barnetik\Tbai\Zuzendu;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class OriginalSignature implements TbaiXml
{
    private string $originalSignature;

    public function __construct(string $originalSignature)
    {
        $this->originalSignature = substr($originalSignature, 0, 100);
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        return $domDocument->createElement('SignatureValueFirmaFactura', $this->originalSignature);
    }

    public function xmlCancel(DOMDocument $domDocument): DOMNode
    {
        return $domDocument->createElement('SignatureValueFirmaAnulacion', $this->originalSignature);
    }

    public static function createFromJson(array $jsonData = []): self
    {
        return new self($jsonData['originalSignature']);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'originalSignature' => [
                    'type' => 'string',
                    'maxLength' => 100
                ]
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            'originalSignature' => $this->originalSignature
        ];
    }
}
