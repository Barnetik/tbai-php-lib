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
}
