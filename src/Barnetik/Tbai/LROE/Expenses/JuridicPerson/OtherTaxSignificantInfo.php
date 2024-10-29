<?php

namespace Barnetik\Tbai\LROE\Expenses\JuridicPerson;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;

class OtherTaxSignificantInfo implements TbaiXml
{
    private ?Date $accountingRecordDate = null;
    private ?string $billingAgreementRecordNumber = null;
    private ?string $externalReference = null;
    private ?SuccededEntity $succededEntity = null;

    private function __construct()
    {
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $otherTaxInfo = $domDocument->createElement('OtraInformacionTrascendenciaTributaria');

        if ($this->accountingRecordDate) {
            $otherTaxInfo->appendChild($domDocument->createElement('FechaRegistroContable', (string) $this->accountingRecordDate));
        }

        if ($this->billingAgreementRecordNumber) {
            $otherTaxInfo->appendChild($domDocument->createElement('NumRegistroAcuerdoFacturacion', $this->billingAgreementRecordNumber));
        }

        if ($this->externalReference) {
            $otherTaxInfo->appendChild($domDocument->createElement('ReferenciaExterna', $this->externalReference));
        }

        if ($this->externalReference) {
            $otherTaxInfo->appendChild($this->succededEntity->xml($domDocument));
        }

        return $otherTaxInfo;
    }

    public static function createFromJson(array $jsonData): self
    {
        $otherTaxInfo = new self();

        if (isset($jsonData['accountingRecordDate'])) {
            $otherTaxInfo->accountingRecordDate = new Date($jsonData['accountingRecordDate']);
        }

        if (isset($jsonData['billingAgreementRecordNumber'])) {
            $otherTaxInfo->billingAgreementRecordNumber = $jsonData['billingAgreementRecordNumber'];
        }

        if (isset($jsonData['externalReference'])) {
            $otherTaxInfo->externalReference = $jsonData['externalReference'];
        }

        if (isset($jsonData['succededEntity'])) {
            $otherTaxInfo->succededEntity = SuccededEntity::createFromJson($jsonData['succededEntity']);
        }

        return $otherTaxInfo;
    }

    public function toArray(): array
    {
        return [
            'accountingRecordDate' => $this->accountingRecordDate ? (string)$this->accountingRecordDate : null,
            'billingAgreementRecordNumber' => $this->billingAgreementRecordNumber,
            'externalReference' => $this->externalReference,
            'succededEntity' => $this->succededEntity ? $this->succededEntity->toArray() : null
        ];
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'accountingRecordDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Fecha de registro contable'
                ],
                'billingAgreementRecordNumber' => [
                    'type' => 'string',
                    'maxLength' => 15,
                    'description' => 'Número de registro del acuerdo de facturación'
                ],
                'externalReference' => [
                    'type' => 'string',
                    'maxLength' => 60,
                    'description' => 'Referencia externa'
                ],
                'succededEntity' => SuccededEntity::docJson()
            ]
        ];
    }
}
