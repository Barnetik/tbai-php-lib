<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidAmmountException;
use Stringable;

class Ammount implements Stringable
{
    private string $value;

    public function __construct(string $ammount, int $intPartMaxLength = 12, int $decLength = 2)
    {
        $this->check($ammount, $intPartMaxLength, $decLength);
        $this->value = $ammount;
    }

    public function check(string $ammount, int $intPartMaxLength = 12, int $decLength = 2): bool
    {
        if (preg_match('/^\d{1,' . $intPartMaxLength . '}\.\d{' . $decLength . '}$/', $ammount, $matches)) {
            return true;
        }

        throw new InvalidAmmountException();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
