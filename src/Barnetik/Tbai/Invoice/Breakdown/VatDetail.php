<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\TypeChecker\Ammount;
use DOMDocument;
use DOMNode;

class VatDetail implements TbaiXml
{
    private string $taxBase;
    private ?string $taxRate = null;
    private ?string $taxQuota = null;
    private ?string $equivalenceRate = null;
    private ?string $equivalenceQuota = null;
    private ?bool $isEquivalenceOperation = null;

    private Ammount $ammountChecker;

    public function __construct(string $taxBase, ?string $taxRate = null, ?string $taxQuota = null, ?string $equivalenceRate = null, ?string $equivalenceQuota = null, ?bool $isEquivalenceOperation = null)
    {
        $this->ammountChecker = new Ammount();
        $this->setTaxBase($taxBase);

        if ($taxRate) {
            $this->setTaxRate($taxRate);
        }
        if ($taxQuota) {
            $this->setTaxQuota($taxQuota);
        }
        if ($equivalenceRate) {
            $this->setEquivalenceRate($equivalenceRate);
        }
        if ($equivalenceQuota) {
            $this->setEquivalenceQuota($equivalenceQuota);
        }

        $this->isEquivalenceOperation = $isEquivalenceOperation;
    }

    protected function setTaxBase(string $taxBase): self
    {
        $this->ammountChecker->check($taxBase, 12);
        $this->taxBase = $taxBase;
        return $this;
    }

    protected function setTaxRate(string $taxRate): self
    {
        $this->ammountChecker->check($taxRate, 3);
        $this->taxRate = $taxRate;
        return $this;
    }

    protected function setTaxQuota(string $taxQuota): self
    {
        $this->ammountChecker->check($taxQuota, 12);
        $this->taxQuota = $taxQuota;
        return $this;
    }

    protected function setEquivalenceRate(string $equivalenceRate): self
    {
        $this->ammountChecker->check($equivalenceRate, 3);
        $this->equivalenceRate = $equivalenceRate;
        return $this;
    }

    protected function setEquivalenceQuota(string $equivalenceQuota): self
    {
        $this->ammountChecker->check($equivalenceQuota, 12);
        $this->equivalenceQuota = $equivalenceQuota;
        return $this;
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
