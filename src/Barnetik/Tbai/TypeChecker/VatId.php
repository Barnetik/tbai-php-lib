<?php

namespace Barnetik\Tbai\TypeChecker;

use Barnetik\Tbai\Exception\InvalidAmmountException;

class VatId
{
    public function check(string $vatId): bool
    {
        if (preg_match('/^(([a-z|A-Z]{1}\d{7}[a-z|A-Z]{1})|(\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\d{8}))$/', $vatId, $matches)) {
            return true;
        }

        throw new InvalidAmmountException();
    }

    public function __invoke(string $vatId): bool
    {
        return $this->check($vatId);
    }
}
