<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidTimeException;
use Barnetik\Tbai\Interfaces\Stringable;

class Time implements Stringable
{
    private string $value;

    public function __construct(string $time)
    {
        $this->check($time);
        $this->value = $time;
    }

    public function check(string $time): bool
    {
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time, $matches)) {
            throw new InvalidTimeException('Wrong time provided');
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
