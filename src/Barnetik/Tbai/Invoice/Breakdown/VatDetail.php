<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;
use DOMXPath;

class VatDetail implements TbaiXml
{
    private Amount $taxBase;
    private ?Amount $taxRate = null;
    private ?Amount $taxQuota = null;
    private ?Amount $equivalenceRate = null;
    private ?Amount $equivalenceQuota = null;
    private ?bool $isEquivalenceOperation = null;

    public function __construct(Amount $taxBase, ?Amount $taxRate = null, ?Amount $taxQuota = null, ?Amount $equivalenceRate = null, ?Amount $equivalenceQuota = null, ?bool $isEquivalenceOperation = null)
    {
        $this->taxBase = $taxBase;
        $this->taxRate = $taxRate ?? null;
        $this->taxQuota = $taxQuota ?? null;
        $this->equivalenceRate = $equivalenceRate ?? null;
        $this->equivalenceQuota = $equivalenceQuota ?? null;
        $this->isEquivalenceOperation = $isEquivalenceOperation ?? null;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $vatDetail = $domDocument->createElement('DetalleIVA');
        $vatDetail->appendChild($domDocument->createElement('BaseImponible', $this->taxBase));

        if ($this->taxRate) {
            $vatDetail->appendChild($domDocument->createElement('TipoImpositivo', $this->taxRate));
        }

        if ($this->taxQuota) {
            $vatDetail->appendChild($domDocument->createElement('CuotaImpuesto', $this->taxQuota));
        }

        if ($this->equivalenceRate) {
            $vatDetail->appendChild($domDocument->createElement('TipoRecargoEquivalencia', $this->equivalenceRate));
        }

        if ($this->equivalenceQuota) {
            $vatDetail->appendChild($domDocument->createElement('CuotaRecargoEquivalencia', $this->equivalenceQuota));
        }

        $vatDetail->appendChild($domDocument->createElement('OperacionEnRecargoDeEquivalenciaORegimenSimplificado', $this->isEquivalenceOperation ? 'S' : 'N'));
        return $vatDetail;
    }

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $taxBase = new Amount($xpath->evaluate('string(BaseImponible)', $contextNode));

        $taxRate = null;
        $taxRateValue = $xpath->evaluate('string(TipoImpositivo)', $contextNode);
        if ($taxRateValue) {
            $taxRate = new Amount($taxRateValue);
        }

        $taxQuota = null;
        $taxQuotaValue = $xpath->evaluate('string(CuotaImpuesto)', $contextNode);
        if ($taxQuotaValue) {
            $taxQuota = new Amount($taxQuotaValue);
        }

        $equivalenceRate = null;
        $equivalenceRateValue = $xpath->evaluate('string(TipoRecargoEquivalencia)', $contextNode);
        if ($equivalenceRateValue) {
            $equivalenceRate = new Amount($equivalenceRateValue);
        }

        $equivalenceQuota = null;
        $equivalenceQuotaValue = $xpath->evaluate('string(CuotaRecargoEquivalencia)', $contextNode);
        if ($equivalenceQuotaValue) {
            $equivalenceQuota = new Amount($equivalenceQuotaValue);
        }

        $isEquivalenceOperation = $xpath->evaluate('OperacionEnRecargoDeEquivalenciaORegimenSimplificado = "S"', $contextNode);

        return new self($taxBase, $taxRate, $taxQuota, $equivalenceRate, $equivalenceQuota, $isEquivalenceOperation);
    }

    public static function createFromJson(array $jsonData): self
    {
        $taxBase = new Amount($jsonData['taxBase']);

        $taxRate = null;
        if (isset($jsonData['taxRate']) && $jsonData['taxRate'] !== '') {
            $taxRate = new Amount($jsonData['taxRate']);
        }

        $taxQuota = null;
        if (isset($jsonData['taxQuota']) && $jsonData['taxQuota'] !== '') {
            $taxQuota = new Amount($jsonData['taxQuota']);
        }

        $equivalenceRate = null;
        if (isset($jsonData['equivalenceRate']) && $jsonData['equivalenceRate'] !== '') {
            $equivalenceRate = new Amount($jsonData['equivalenceRate']);
        }

        $equivalenceQuota = null;
        if (isset($jsonData['equivalenceQuota']) && $jsonData['equivalenceQuota'] !== '') {
            $equivalenceQuota = new Amount($jsonData['equivalenceQuota']);
        }

        $isEquivalenceOperation = $jsonData['isEquivalenceOperation'] ?? false;

        return new self($taxBase, $taxRate, $taxQuota, $equivalenceRate, $equivalenceQuota, $isEquivalenceOperation);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'taxBase' => [
                    'type' => 'string',
                    'description' => 'Zerga oinarria (2 dezimalekin) - Base imponible (2 decimales)'
                ],
                'taxRate' => [
                    'type' => 'string',
                    'description' => 'Zerga tasa (2 dezimalekin) - Tipo impositivo (2 decimales)'
                ],
                'taxQuota' => [
                    'type' => 'string',
                    'description' => 'Zergaren kuota (2 dezimalekin) - Cuota del impuesto (2 decimales)'
                ],
                'equivalenceRate' => [
                    'type' => 'string',
                    'description' => 'Baliokidetasun errekarguaren tasa (2 dezimalekin) - Tipo del recargo de equivalencia (2 decimales)'
                ],
                'equivalenceQuota' => [
                    'type' => 'string',
                    'description' => 'Baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia (2 decimales)'
                ],
                'isEquivalenceOperation' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' =>  'Baliokidetasun errekargudun eragiketa edo araubide erraztuko eragiketa bat da - Es una operación en recargo de equivalencia o Régimen simplificado'
                ]
            ],
            'required' => ['taxBase']
        ];
    }

    public function toArray(): array
    {
        return [
            'taxBase' => (string)$this->taxBase,
            'taxRate' => $this->taxRate ? (string)$this->taxRate : null,
            'taxQuota' => $this->taxQuota ? (string)$this->taxQuota : null,
            'equivalenceRate' => $this->equivalenceRate ? (string)$this->equivalenceRate : null,
            'equivalenceQuota' => $this->equivalenceQuota ? (string)$this->equivalenceQuota : null,
            'isEquivalenceOperation' => $this->isEquivalenceOperation ?? null,
        ];
    }
}
