<?php

namespace Barnetik\Tbai\LROE\Expenses;

use Barnetik\Tbai\Interfaces\TbaiXml;

abstract class AbstractTaxesInfo implements TbaiXml
{
    protected array $taxesInfo = [];

    protected function __construct()
    {
    }

    protected function addTaxInfo(AbstractTaxInfo $taxInfo): static
    {
        $this->taxesInfo[] = $taxInfo;
        return $this;
    }

    public function toArray(): array
    {
        return array_map(function ($taxInfo) {
            return $taxInfo->toArray();
        }, $this->taxesInfo);
    }
}
