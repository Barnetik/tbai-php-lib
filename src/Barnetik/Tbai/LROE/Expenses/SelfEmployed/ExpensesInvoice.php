<?php

namespace Barnetik\Tbai\LROE\Expenses\SelfEmployed;

use Barnetik\Tbai\LROE\Expenses\Issuer;
use Barnetik\Tbai\LROE\Expenses\Header;
use Barnetik\Tbai\LROE\Expenses\Interfaces\ExpensesInvoice as InterfacesExpensesInvoice;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\Data;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\TaxesInfo;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;

class ExpensesInvoice implements InterfacesExpensesInvoice
{
    private string $recipientVatId;
    private string $recipientName;

    private Issuer $issuer;
    private Header $header;
    private Data $data;
    private TaxesInfo $taxesInfo;


    private function __construct(string $recipientVatId, string $recipientName, Issuer $issuer, Header $header, Data $data, TaxesInfo $taxesInfo)
    {
        $this->recipientVatId = $recipientVatId;
        $this->recipientName = $recipientName;
        $this->issuer = $issuer;
        $this->header = $header;
        $this->data = $data;
        $this->taxesInfo = $taxesInfo;
    }

    public function selfEmployed(): bool
    {
        return true;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $expenses = $domDocument->createElement('Gasto');

        $expenses->appendChild($this->issuer->xml($domDocument));

        $expenses->appendChild($this->header->xml($domDocument));
        $expenses->appendChild($this->data->xml($domDocument));
        $expenses->appendChild($this->taxesInfo->xml($domDocument));

        return $expenses;
    }

    public static function createFromJson(array $jsonData): self
    {
        $recipientVatId = $jsonData['recipient']['vatId'];
        $recipientName = $jsonData['recipient']['name'];

        $issuer = Issuer::createFromJson($jsonData['issuer']);
        $header = Header::createFromJson($jsonData['header']);

        $data = Data::createFromJson($jsonData['data']);
        $taxesInfo = TaxesInfo::createFromJson($jsonData['taxesInfo']);

        $expenses = new self($recipientVatId, $recipientName, $issuer, $header, $data, $taxesInfo);
        return $expenses;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'recipient' => [
                    'type' => 'object',
                    'properties' => [
                        'vatId' => [
                            'type' => 'string'
                        ],
                        'name' => [
                            'type' => 'string'
                        ]
                    ],
                    'required' => ['vatId', 'name']
                ],
                'issuer' => Issuer::docJson(),
                'header' => Header::docJson(),
                'data' => Data::docJson(),
                'taxesInfo' => TaxesInfo::docJson(),
            ],
            'required' => ['recipient', 'issuer', 'header', 'data', 'taxesInfo']
        ];
    }

    public function toArray(): array
    {
        return [
            'recipient' => [
                'vatId' => $this->recipientVatId,
                'name' => $this->recipientName,
            ],
            'issuer' => $this->issuer->toArray(),
            'header' => $this->header->toArray(),
            'data' => $this->data->toArray(),
            'taxesInfo' => $this->taxesInfo->toArray(),
        ];
    }

    public function receptionDate(): Date
    {
        return $this->header->receptionDate();
    }

    public function recipientVatId(): string
    {
        return $this->recipientVatId;
    }

    public function recipientName(): string
    {
        return $this->recipientName;
    }
}
