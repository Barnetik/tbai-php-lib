<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\TypeChecker\Ammount;

class VatDetail
{
    private string $taxBase;
    private ?string $taxRate;
    private ?string $taxQuota;
    private ?string $equivalenceRate;
    private ?string $equivalenceQuota;
    private ?bool $isEquivalenceOperation;

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
}
