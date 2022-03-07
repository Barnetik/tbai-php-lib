<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;

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

    public static function createFromJson(array $jsonData): self
    {
        $taxBase = new Amount($jsonData['taxBase']);

        $taxRate = null;
        if (isset($jsonData['taxRate'])) {
            $taxRate = new Amount($jsonData['taxRate']);
        }

        $taxQuota = null;
        if (isset($jsonData['taxQuota'])) {
            $taxQuota = new Amount($jsonData['taxQuota']);
        }

        $equivalenceRate = null;
        if (isset($jsonData['equi$equivalenceRate'])) {
            $equivalenceRate = new Amount($jsonData['equivalenceRate']);
        }

        $equivalenceQuota = null;
        if (isset($jsonData['equivalenceQuota'])) {
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
            'taxQuota' => $this->taxRate ? (string)$this->taxRate : null,
            'equivalenceRate' => $this->equivalenceRate ? (string)$this->equivalenceRate : null,
            'equivalenceQuota' => $this->equivalenceQuota ? (string)$this->equivalenceQuota : null,
            'isEquivalenceOperation' => $this->isEquivalenceOperation ?? null,
        ];
    }
}
