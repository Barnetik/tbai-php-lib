<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\AmmountChecker;

class InvoiceData
{
    private string $description;
    private string $total;
    private ?string $supportedRetention;
    private ?string $taxBaseCost;
    private array $details = [];
    private AmmountChecker $ammountChecker;

    public function __construct(string $description, string $total, ?string $supportedRetention = null, ?string $taxBaseCost = null)
    {
        $this->ammountChecker = new AmmountChecker();

        $this->description = $description;
        $this->setTotal($total);

        if ($supportedRetention) {
            $this->setSupportedRetention($supportedRetention);
        }

        if ($taxBaseCost) {
            $this->setTaxBaseCost($taxBaseCost);
        }
    }

    private function setTotal(string $total): self
    {
        $this->ammountChecker->check($total, 12);

        $this->total = $total;
        return $this;
    }

    private function setSupportedRetention(string $supportedRetention): self
    {
        $this->ammountChecker->check($supportedRetention, 12);

        $this->supportedRetention = $supportedRetention;
        return $this;
    }

    private function setTaxBaseCost(string $taxBaseCost): self
    {
        $this->ammountChecker->check($taxBaseCost, 12);

        $this->taxBaseCost = $taxBaseCost;
        return $this;
    }
}
