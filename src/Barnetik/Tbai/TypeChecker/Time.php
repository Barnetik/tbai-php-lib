<?php

namespace Barnetik\Tbai\TypeChecker;

use Barnetik\Tbai\Exception\InvalidTimeException;
use DateTimeImmutable;

class Time
{
    public function check(string $time): bool
    {
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time, $matches)) {
            throw new InvalidTimeException('Wrong time provided');
        }

        return true;
    }

    public function __invoke(string $time): bool
    {
        return $this->check($time);
    }
}
