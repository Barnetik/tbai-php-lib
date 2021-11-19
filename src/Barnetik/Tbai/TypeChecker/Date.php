<?php

namespace Barnetik\Tbai\TypeChecker;

use Barnetik\Tbai\Exception\InvalidDateException;
use DateTimeImmutable;

class Date
{
    public function check(string $date): bool
    {
        if (false === DateTimeImmutable::createFromFormat("d-m-Y", $date)) {
            throw new InvalidDateException('Wrong date provided');
        }

        return true;
    }

    public function __invoke(string $date): bool
    {
        return $this->check($date);
    }
}
