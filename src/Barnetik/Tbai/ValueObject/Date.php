<?php

namespace Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidDateException;
use DateTimeImmutable;
use Stringable;

class Date implements Stringable
{
    private DateTimeImmutable $value;

    public function __construct(string $date)
    {
        $this->check($date);
        $this->value = DateTimeImmutable::createFromFormat("d-m-Y", $date);
    }

    public function check(string $date): bool
    {
        $value = DateTimeImmutable::createFromFormat("d-m-Y", $date);
        if (false === $value) {
            throw new InvalidDateException('Wrong date provided');
        }

        return true;
    }

    public function short(): string
    {
        return $this->value->format('dmy');
    }

    public function __toString(): string
    {
        return $this->value->format('d-m-Y');
    }
}
