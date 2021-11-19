<?php

namespace Barnetik\Tbai\TypeChecker;

use Barnetik\Tbai\Exception\InvalidAmmountException;

class Ammount
{
    public function check(string $ammount, int $intPartMaxLength = 12): bool
    {
        if (preg_match('/^\d{1,' . $intPartMaxLength . '}\.\d{2}$/', $ammount, $matches)) {
            return true;
        }

        throw new InvalidAmmountException();
    }

    public function __invoke(string $ammount, int $intPartMaxLength = 12): bool
    {
        return $this->check($ammount, $intPartMaxLength);
    }
}
