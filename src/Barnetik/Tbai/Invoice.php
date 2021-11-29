<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;

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

        $invoice->append(
            $this->header->xml($domDocument),
            $this->data->xml($domDocument),
            $this->breakdown->xml($domDocument)
        );

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

    public function totalAmmount(): Ammount
    {
        return $this->data->total();
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
            'types' => 'object',
            'properties' => [
                'header' => Header::docJson(),
                'data' => Data::docJson(),
                'breakdown' => Breakdown::docJson()
            ],
            'required' => ['header', 'data', 'breakdown']
        ];
    }
}
