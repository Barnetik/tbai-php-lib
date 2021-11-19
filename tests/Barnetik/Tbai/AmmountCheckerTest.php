<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\TypeChecker\Ammount;
use Barnetik\Tbai\Exception\InvalidAmmountException;
use PHPUnit\Framework\TestCase;

class AmmountCheckerTest extends TestCase
{
    public function test_checker_throws_exception_if_ammount_is_not_valid(): void
    {
        $checker = new Ammount();

        try {
            $ammount = "12,04";
            $checker($ammount, 2);
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }

        try {
            $ammount = "12.4";
            $checker($ammount);
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }

        try {
            $ammount = "123456.02";
            $checker($ammount, 2);
            $this->fail();
        } catch (InvalidAmmountException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_checker_returns_true_if_ammount_is_valid(): void
    {
        $checker = new Ammount();

        $ammount = "123456.02";
        $this->assertTrue($checker($ammount));

        $ammount = "123456.02";
        $this->assertTrue($checker($ammount, 6));
    }
}
