<?php

namespace Barnetik\Tbai\LROE\Expenses;

use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Amount;
use InvalidArgumentException;

class JuridicPersonTaxInfo extends AbstractTaxInfo
{
    const PURCHASE_TYPE_COMMON_GOODS = 'C';
    const PURCHASE_TYPE_EXPENSES = 'G';
    const PURCHASE_TYPE_INVESTMENTS = 'I';

    private string $purchaseType;
    private bool $taxablePersonReversal;
    private Amount $taxBase;
    private ?Amount $taxRate = null;
    private ?Amount $supportedTaxQuota = null;
    private ?Amount $deductibleTaxQuota = null;
    private ?Amount $reagypCompensationPercent = null;
    private ?Amount $reagypCompensationAmount = null;

    private function __construct(string $purchaseType, bool $taxablePersonReversal, Amount $taxBase)
    {
        $this->setPurchaseType($purchaseType);
        $this->taxablePersonReversal = $taxablePersonReversal;
        $this->taxBase = $taxBase;
    }

    private static function validPurchaseTypeValues(): array
    {
        return [
            self::PURCHASE_TYPE_COMMON_GOODS,
            self::PURCHASE_TYPE_EXPENSES,
            self::PURCHASE_TYPE_INVESTMENTS
        ];
    }

    private function setPurchaseType(string $purchaseType): self
    {
        if (!in_array($purchaseType, self::validPurchaseTypeValues())) {
            throw new InvalidArgumentException('Wrong PurchaseType value');
        }
        $this->purchaseType = $purchaseType;

        return $this;
    }


    public function xml(DOMDocument $domDocument): DOMNode
    {
        $taxInfo = $domDocument->createElement('DetalleIVA');

        $taxInfo->appendChild($domDocument->createElement('CompraBienesCorrientesGastosBienesInversion', $this->purchaseType));
        $taxInfo->appendChild($domDocument->createElement('InversionSujetoPasivo', $this->taxablePersonReversal ? 'S' : 'N'));
        $taxInfo->appendChild($domDocument->createElement('BaseImponible', (string)$this->taxBase));
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('TipoImpositivo'), $this->taxRate);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CuotaIVASoportada'), $this->supportedTaxQuota);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('CuotaIVADeducible'), $this->deductibleTaxQuota);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('PorcentajeCompensacionREAGYP'), $this->reagypCompensationPercent);
        $this->appendOptionalXml($taxInfo, $domDocument->createElement('ImporteCompensacionREAGYP'), $this->reagypCompensationAmount);

        return $taxInfo;
    }

    public static function createFromJson(array $jsonData): self
    {
        $taxInfo = new self($jsonData['purchaseType'], (bool)($jsonData['taxablePersonReversal'] ?? false), new Amount($jsonData['taxBase']));

        if (isset($jsonData['taxRate'])) {
            $taxInfo->taxRate = new Amount($jsonData['taxRate'], 3, 2);
        }

        if (isset($jsonData['supportedTaxQuota'])) {
            $taxInfo->supportedTaxQuota = new Amount($jsonData['supportedTaxQuota']);
        }

        if (isset($jsonData['deductibleTaxQuota'])) {
            $taxInfo->deductibleTaxQuota = new Amount($jsonData['deductibleTaxQuota']);
        }

        if (isset($jsonData['reagypCompensationPercent'])) {
            $taxInfo->reagypCompensationPercent = new Amount($jsonData['reagypCompensationPercent'], 3, 2);
        }

        if (isset($jsonData['reagypCompensationAmount'])) {
            $taxInfo->reagypCompensationAmount = new Amount($jsonData['reagypCompensationAmount']);
        }

        return $taxInfo;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
            ],
            'required' => ['purchaseType', 'taxBase']
        ];
    }

    public function toArray(): array
    {
        return [
        ];
    }
}
