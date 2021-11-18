<?php

namespace Barnetik\Tbai\Invoice\Data;

use Barnetik\Tbai\AmmountChecker;

class Detail
{
    private string $description;
    private string $quantity;
    private string $unitPrice;
    private string $discount;
    private string $totalAmmount;

    private AmmountChecker $ammountChecker;

    public function __construct(string $description, string $unitPrice, string $quantity, string $totalAmmount, string $discount = null)
    {
        $this->description = $description;
        $this->setUnitPrice($unitPrice);
        $this->setQuantity($quantity);
        $this->setTotalAmmount($totalAmmount);
        $this->setDiscount($discount);
    }

    protected function setUnitPrice(string $unitPrice)
    {
        $this->ammountChecker->check($unitPrice);

        $this->unitPrice = $unitPrice;
        return $this;
    }

    protected function setQuantity(string $quantity)
    {
        $this->ammountChecker->check($quantity);

        $this->quantity = $quantity;
        return $this;
    }

    protected function setTotalAmmount(string $totalAmmount)
    {
        $this->ammountChecker->check($totalAmmount);

        $this->totalAmmount = $totalAmmount;
        return $this;
    }

    protected function setDiscount(string $discount)
    {
        $this->ammountChecker->check($discount);

        $this->discount = $discount;
        return $this;
    }
}
