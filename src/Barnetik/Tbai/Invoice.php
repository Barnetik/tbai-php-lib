<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Invoice implements TbaiXml
{
    private Header $header;
    private Data $data;
    private Breakdown $breakdown;

    public function __construct(Header $header, Data $data, Breakdown $breakdown)
    {
        $this->header = $header;
        $this->data = $data;
        $this->breakdown = $breakdown;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $invoice = $domDocument->createElement('Factura');

        $invoice->appendChild($this->header->xml($domDocument));
        $invoice->appendChild($this->data->xml($domDocument));
        $invoice->appendChild($this->breakdown->xml($domDocument));

        return $invoice;
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

    public function header(): Header
    {
        return $this->header;
    }

    public function totalAmount(): Amount
    {
        return $this->data->total();
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $header = Header::createFromXml($xpath);
        $data = Data::createFromXml($xpath);
        $breakdown = Breakdown::createFromXml($xpath);

        return new self($header, $data, $breakdown);
    }

    public static function createFromJson(array $jsonData): self
    {
        $header = Header::createFromJson($jsonData['header']);
        $data = Data::createFromJson($jsonData['data']);
        $breakdown = Breakdown::createFromJson($jsonData['breakdown']);
        $invoice = new Invoice($header, $data, $breakdown);
        return $invoice;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'header' => Header::docJson(),
                'data' => Data::docJson(),
                'breakdown' => Breakdown::docJson()
            ],
            'required' => ['header', 'data', 'breakdown']
        ];
    }

    public function toArray(): array
    {
        return [
            'header' => $this->header->toArray(),
            'data' => $this->data->toArray(),
            'breakdown' => $this->breakdown->toArray(),
        ];
    }
}
