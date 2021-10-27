<?php

namespace Barnetik\Tbai;

class Emisor
{
    protected $taxId;
    protected $name;

    private function __construct($taxId, $name)
    {
        $this->taxId = $taxId;
        $this->name = $name;
    }

    public function taxId()
    {
        return $this->taxId;
    }

    public function name()
    {
        return $this->name;
    }
}
