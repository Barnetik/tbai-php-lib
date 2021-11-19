<?php

namespace Barnetik\Tbai\Invoice\Data;

use Barnetik\Tbai\ValueObject\Ammount;

class Detail
{
    private string $description;
    private Ammount $quantity;
    private Ammount $unitPrice;
    private Ammount $discount;
    private Ammount $totalAmmount;

    public function __construct(string $description, Ammount $unitPrice, Ammount $quantity, Ammount $totalAmmount, Ammount $discount = null)
    {
        $this->description = $description;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
        $this->totalAmmount = $totalAmmount;
        $this->discount = $discount;
    }
}
