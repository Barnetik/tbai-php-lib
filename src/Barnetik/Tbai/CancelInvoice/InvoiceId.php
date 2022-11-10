<?php

namespace Barnetik\Tbai\CancelInvoice;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\CancelInvoice\Header;
use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use DOMXPath;

class InvoiceId implements TbaiXml
{
    private Issuer $issuer;
    private Header $header;

    public function __construct(Issuer $issuer, Header $header)
    {
        $this->issuer = $issuer;
        $this->header = $header;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $invoiceId = $domDocument->createElement('IDFactura');

        $invoiceId->appendChild($this->issuer->xml($domDocument));
        $invoiceId->appendChild($this->header->xml($domDocument));

        return $invoiceId;
    }

    public function expeditionDate(): Date
    {
        return $this->header->expeditionDate();
    }

    public function series(): string
    {
        return $this->header->series();
    }

    public function invoiceNumber(): string
    {
        return $this->header->invoiceNumber();
    }


    public function issuerVatId(): VatId
    {
        return $this->issuer->vatId();
    }

    public function issuerName(): string
    {
        return $this->issuer->name();
    }


    public static function createFromJson(array $jsonData): self
    {
        $issuer = Issuer::createFromJson($jsonData['issuer']);
        $header = Header::createFromJson($jsonData['header']);
        $invoiceId = new InvoiceId($issuer, $header);
        return $invoiceId;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $issuer = Issuer::createFromXml($xpath);
        $header = Header::createFromXml($xpath);

        return new self($issuer, $header);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'issuer' => Issuer::docJson(),
                'header' => Header::docJson(),
            ],
            'required' => ['header', 'data', 'breakdown']
        ];
    }

    public function toArray(): array
    {
        return [
            'issuer' => $this->issuer->toArray(),
            'header' => $this->header->toArray(),
        ];
    }
}
