<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Issuer implements TbaiXml
{
    protected VatId $vatId;
    protected string $name;

    public function __construct(VatId $vatId, string $name)
    {
        $this->vatId = $vatId;
        $this->name = $name;
    }

    public function vatId(): VatId
    {
        return $this->vatId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $issuer = $domDocument->createElement('Emisor');
        $issuer->appendChild($domDocument->createElement('NIF', $this->vatId));
        $issuer->appendChild($domDocument->createElement('ApellidosNombreRazonSocial', htmlspecialchars($this->name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')));
        return $issuer;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $vatId = new VatId($xpath->evaluate('string(/T:TicketBai/Sujetos/Emisor/NIF)'));
        $name = $xpath->evaluate('string(/T:TicketBai/Sujetos/Emisor/ApellidosNombreRazonSocial)');

        return new Issuer($vatId, $name);
    }

    public static function createFromJson(array $jsonData): self
    {
        $vatId = new VatId($jsonData['vatId']);
        $name = $jsonData['name'];
        $issuer = new Issuer($vatId, $name);
        return $issuer;
    }


    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'vatId' => [
                    'type' => 'string',
                    'pattern' => '^(([a-z|A-Z]{1}\d{7}[a-z|A-Z]{1})|(\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\d{8}))$',
                    'description' => 'IFZ - NIF'
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 120,
                    'description' => 'Abizenak eta izena edo Sozietatearen izena - Apellidos y nombre o Razón social'
                ]
            ],
            'required' => ['vatId', 'name']
        ];
    }

    public function toArray(): array
    {
        return [
            'vatId' => (string)$this->vatId,
            'name' => $this->name,
        ];
    }
}
