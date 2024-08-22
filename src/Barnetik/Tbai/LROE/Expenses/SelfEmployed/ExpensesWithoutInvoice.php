<?php

namespace Barnetik\Tbai\LROE\Expenses\SelfEmployed;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;

class ExpensesWithoutInvoice implements TbaiXml
{
    private string $recipientVatId;
    private string $recipientName;

    private ?DeclaredSupplierCounterpart $declaredSupplierCounterpart;
    private Date $operationDate;
    private Date $receptionDate;
    private Income $income;


    private function __construct(string $recipientVatId, string $recipientName, Date $operationDate, Date $receptionDate, Income $income, DeclaredSupplierCounterpart $declaredSupplierCounterpart = null)
    {
        $this->recipientVatId = $recipientVatId;
        $this->recipientName = $recipientName;

        $this->declaredSupplierCounterpart = $declaredSupplierCounterpart;
        $this->operationDate = $operationDate;
        $this->receptionDate = $receptionDate;
        $this->income = $income;
    }

    public function selfEmployed(): bool
    {
        return true;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $expenses = $domDocument->createElement('Gasto');

        if ($this->declaredSupplierCounterpart) {
            $expenses->appendChild($this->declaredSupplierCounterpart->xml($domDocument));
        }

        $expenses->appendChild($domDocument->createElement('FechaOperacion', (string) $this->operationDate));
        $expenses->appendChild($this->income->xml($domDocument));

        return $expenses;
    }

    public static function createFromJson(array $jsonData): self
    {
        $recipientVatId = $jsonData['recipient']['vatId'];
        $recipientName = $jsonData['recipient']['name'];

        $operationDate = new Date($jsonData['operationDate']);
        $receptionDate = new Date($jsonData['receptionDate']);
        $income = Income::createFromJson($jsonData['income']);

        $declaredSupplierCounterpart = null;
        if (array_key_exists('declaredSupplierCounterpart', $jsonData) && !is_null($jsonData['declaredSupplierCounterpart'])) {
            $declaredSupplierCounterpart = DeclaredSupplierCounterpart::createFromJson($jsonData['declaredSupplierCounterpart']);
        }

        return new self($recipientVatId, $recipientName, $operationDate, $receptionDate, $income, $declaredSupplierCounterpart);
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
                'declaredSupplierCounterpart' => DeclaredSupplierCounterpart::docJson(),
                'operationDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Fakturaren eragiketa data (adib: 21-12-2020) - Fecha de operación de factura (ej: 21-12-2020)'
                ],
                'receptionDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Faktura jasotako data (adib: 21-12-2020) - Fecha de recepción de factura (ej: 21-12-2020)'
                ],
                'income' => Income::docJson(),
            ],
            'required' => ['recipient', 'operationDate', 'receptionDate', 'income']
        ];
    }

    public function toArray(): array
    {
        return [
            'recipient' => [
                'vatId' => $this->recipientVatId,
                'name' => $this->recipientName,
            ],
            'declaredSupplierCounterpart' => $this->declaredSupplierCounterpart ? $this->declaredSupplierCounterpart->toArray() : [],
            'operationDate' => (string)$this->operationDate,
            'receptionDate' => (string)$this->receptionDate,
            'income' => $this->income->toArray(),
        ];
    }

    public function recipientVatId(): string
    {
        return $this->recipientVatId;
    }

    public function recipientName(): string
    {
        return $this->recipientName;
    }

    public function receptionDate(): Date
    {
        return $this->receptionDate;
    }
}
