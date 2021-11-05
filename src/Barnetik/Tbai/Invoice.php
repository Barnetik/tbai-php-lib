<?php

namespace Barnetik\Tbai;

class Invoice
{
    const TAX_TYPE_4 = 4;
    const TAX_TYPE_10 = 10;
    const TAX_TYPE_21 = 21;

    protected string $invoiceNumber;
    protected string $description;

    protected string $beforeTaxTotal;
    protected string $afterTaxTotal;
    protected int $taxType;

    protected array $lines = [];

    public function __construct(string $invoiceNumber, string $description, string $beforeTaxTotal, string $afterTaxTotal, int $taxType)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->description = $description;

        $this->beforeTaxTotal = $beforeTaxTotal;
        $this->afterTaxTotal = $afterTaxTotal;
        $this->taxType = $taxType;
    }

    public function addLine(InvoiceLine $line): self
    {
        array_push($this->lines, $line);
        return $this;
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function beforeTaxTotal(): string
    {
        return $this->beforeTaxTotal;
    }

    public function afterTaxTotal(): string
    {
        return $this->afterTaxTotal;
    }

    public function taxType(): int
    {
        return $this->taxType;
    }

    public function lines(): array
    {
        return $this->lines;
    }
}
