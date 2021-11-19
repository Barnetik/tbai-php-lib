<?php

namespace Barnetik\TbaiValueObject;

use Barnetik\Tbai\Exception\InvalidAmmountException;
use Barnetik\Tbai\ValueObject\Ammount;
use PHPUnit\Framework\TestCase;

class AmmountTest extends TestCase
{
    public function test_checker_throws_exception_if_ammount_is_not_valid(): void
    {
        try {
            $ammount = new Ammount("12,04", 2);
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }

        try {
            $ammount = new Ammount("12.4");
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }

        try {
            $ammount = new Ammount("123456.02", 2);
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }
    }
}
