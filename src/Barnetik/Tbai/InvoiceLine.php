<?php

namespace Barnetik\Tbai;

class InvoiceLine
{
    const TAX_TYPE_4 = 4;
    const TAX_TYPE_10 = 10;
    const TAX_TYPE_21 = 21;

    protected string $description;
    protected string $beforeTaxPrice;
    protected string $afterTaxPrice;
    protected int $taxType;

    public function __construct(string $description, string $beforeTaxPrice, string $afterTaxPrice, int $taxType)
    {
        $this->description = $description;
        $this->beforeTaxPrice = $beforeTaxPrice;
        $this->afterTaxPrice = $afterTaxPrice;
        $this->taxType = $taxType;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function beforeTaxPrice(): string
    {
        return $this->beforeTaxPrice;
    }

    public function afterTaxPrice(): string
    {
        return $this->afterTaxPrice;
    }

    public function taxType(): int
    {
        return $this->taxType;
    }
}
