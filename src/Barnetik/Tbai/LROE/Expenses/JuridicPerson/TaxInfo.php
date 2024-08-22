<?php

namespace Barnetik\Tbai\LROE\Expenses\JuridicPerson;

use Barnetik\Tbai\LROE\Expenses\Shared\AbstractTaxInfo;
use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Amount;
use InvalidArgumentException;

class TaxInfo extends AbstractTaxInfo
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
                'purchaseType' => [
                    'type' => 'string',
                    'enum' => self::validPurchaseTypeValues(),
                    'description' => '
Tipo de gasto:
 * C: Compra de bienes corrientes
 * G: Gastos
 * I: Adquisición de bienes de inversión
                ',
                ],
                'taxablePersonReversal' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Inversión del sujeto pasivo'
                ],
                'taxBase' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Base imponible (2 decimales)'
                ],
                'taxRate' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,3}(\.\d{0,2})?$',
                    'description' => 'Tipo impositivo (2 decimales)'
                ],
                'supportedTaxQuota' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Cuota IVA Soportada (2 decimales)'
                ],
                'deductibleTaxQuota' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Cuota IVA Deducible (2 decimales)'
                ],
                'reagypCompensationPercent' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,3}(\.\d{0,2})?$',
                    'description' => 'Porcentaje compensación REAGYP (2 decimales)'
                ],
                'reagypCompensationAmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Importe de compensación REAGYP (2 decimales)'
                ],
            ],
            'required' => ['purchaseType', 'taxBase']
        ];
    }

    public function toArray(): array
    {
        return [
            'purchaseType' => $this->purchaseType,
            'taxablePersonReversal' => $this->taxablePersonReversal,
            'taxBase' => (string) $this->taxBase,
            'taxRate' => $this->taxRate ? (string)$this->taxRate : null,
            'supportedTaxQuota' => $this->supportedTaxQuota ? (string)$this->supportedTaxQuota : null,
            'deductibleTaxQuota' => $this->deductibleTaxQuota ? (string)$this->deductibleTaxQuota : null,
            'reagypCompensationPercent' => $this->reagypCompensationPercent ? (string)$this->reagypCompensationPercent : null,
            'reagypCompensationAmount' => $this->reagypCompensationAmount ? (string)$this->reagypCompensationAmount : null,
        ];
    }
}
