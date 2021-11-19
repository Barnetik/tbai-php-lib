<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidDateException;
use DateTimeImmutable;
use Stringable;

class Date implements Stringable
{
    private string $value;

    public function __construct(string $date)
    {
        $this->check($date);
        $this->value = $date;
    }

    public function check(string $date): bool
    {
        if (false === DateTimeImmutable::createFromFormat("d-m-Y", $date)) {
            throw new InvalidDateException('Wrong date provided');
        }

        return true;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
