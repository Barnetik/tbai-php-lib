<?php

namespace Barnetik\Tbai\Subject;

class Emitter
{
    protected string $taxId;
    protected string $name;

    public function __construct(string $taxId, string $name)
    {
        $this->taxId = $taxId;
        $this->name = $name;
    }

    public function taxId(): string
    {
        return $this->taxId;
    }

    public function name(): string
    {
        return $this->name;
    }
}
