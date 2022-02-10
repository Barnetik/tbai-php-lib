<?php

namespace Test\Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidAmountException;
use Barnetik\Tbai\ValueObject\Amount;
use PHPUnit\Framework\TestCase;

class AmountTest extends TestCase
{
    public function test_throws_exception_if_amount_is_not_valid(): void
    {
        try {
            $amount = new Amount("12,04", 2);
            $this->fail();
        } catch (InvalidAmountException $e) {
            $this->assertTrue(true);
        }

        // try {
        //     $amount = new Amount("12.4");
        //     $this->fail();
        // } catch (InvalidAmountException $e) {
        //     $this->assertTrue(true);
        // }

        try {
            $amount = new Amount("123456.02", 2);
            $this->fail();
        } catch (InvalidAmountException $e) {
            $this->assertTrue(true);
        }
    }
}
