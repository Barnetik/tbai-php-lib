<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Exception\InvalidAmmountException;

class AmmountChecker
{
    public function __invoke(string $ammount, $intPartMaxLength = 12)
    {
        if (preg_match('/^\d{1,' . $intPartMaxLength . '},\d{2}$/', $ammount, $matches)) {
            return true;
        }

        throw new InvalidAmmountException();
    }
}
