<?php

namespace Barnetik\Tbai\Invoice;

class InvoiceData
{
    private string $description;
    private string $total;
    private ?string $supportedRetention;
    private ?string $taxBaseCost;
    private array $details = [];

    public function __construct(string $description, string $total, ?string $supportedRetention = null, ?string $taxBaseCost = null)
    {
        $this->description = $description;
        $this->total = $total;
        $this->supportedRetention = $supportedRetention;
        $this->taxBaseCost = $taxBaseCost;
    }
}
