<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidAmountException;
use Barnetik\Tbai\Interfaces\Stringable;

class Amount implements Stringable
{
    private string $value;

    public function __construct(string $amount, int $intPartMaxLength = 12, int $decLength = 2)
    {
        $this->check($amount, $intPartMaxLength, $decLength);
        $this->value = $amount;
    }

    public function check(string $amount, int $intPartMaxLength = 12, int $decLength = 2): bool
    {
        if (preg_match('/^-?\d{1,' . $intPartMaxLength . '}(\.\d{0,' . $decLength . '})?$/', $amount, $matches)) {
            return true;
        }

        throw new InvalidAmountException('Invalid amount value: ' . $amount);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
