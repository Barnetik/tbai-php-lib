<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use DOMDocument;
use DOMNode;

class VatDetail implements TbaiXml
{
    private Ammount $taxBase;
    private ?Ammount $taxRate = null;
    private ?Ammount $taxQuota = null;
    private ?Ammount $equivalenceRate = null;
    private ?Ammount $equivalenceQuota = null;
    private ?bool $isEquivalenceOperation = null;

    public function __construct(Ammount $taxBase, ?Ammount $taxRate = null, ?Ammount $taxQuota = null, ?Ammount $equivalenceRate = null, ?Ammount $equivalenceQuota = null, ?bool $isEquivalenceOperation = null)
    {
        $this->taxBase = $taxBase;

        if ($taxRate) {
            $this->taxRate = $taxRate;
        }
        if ($taxQuota) {
            $this->taxQuota = $taxQuota;
        }
        if ($equivalenceRate) {
            $this->equivalenceRate = $equivalenceRate;
        }
        if ($equivalenceQuota) {
            $this->equivalenceQuota = $equivalenceQuota;
        }

        $this->isEquivalenceOperation = $isEquivalenceOperation;
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
                    'description' => 'Zerga tasa - Tipo impositivo'
                ],
                'taxQuota' => [
                    'type' => 'string',
                    'description' => 'Zergaren kuota - Cuota del impuesto'
                ],
                'equivalenceRate' => [
                    'type' => 'string',
                    'description' => 'Baliokidetasun errekarguaren tasa - Tipo del recargo de equivalencia'
                ],
                'equivalenceQuota' => [
                    'type' => 'string',
                    'description' => 'Baliokidetasun errekarguaren kuota - Cuota del recargo de equivalencia'
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
}
