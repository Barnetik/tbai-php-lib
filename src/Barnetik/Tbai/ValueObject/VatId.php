<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidVatIdException;
use Stringable;

class VatId implements Stringable
{
    private string $value;

    public function __construct(string $vatId)
    {
        $this->check($vatId);
        $this->value = $vatId;
    }

    public function check(string $vatId): bool
    {
        if (!preg_match('/^(([a-z|A-Z]{1}\d{7}[a-z|A-Z]{1})|(\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\d{8}))$/', $vatId, $matches)) {
            throw new InvalidVatIdException('Wrong VATId provided');
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
