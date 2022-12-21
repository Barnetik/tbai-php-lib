<?php

namespace Test\Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidVatIdException;
use Barnetik\Tbai\ValueObject\VatId;
use PHPUnit\Framework\TestCase;

class VatIdTest extends TestCase
{
    public function test_ifz_with_wrong_format_throw_exception(): void
    {
        try {
            new VatId("1234567-S", VatId::VAT_ID_TYPE_IFZ);
            $this->fail();
        } catch (InvalidVatIdException $e) {
        }

        try {
            new VatId("0134567S", VatId::VAT_ID_TYPE_IFZ);
            $this->fail();
        } catch (InvalidVatIdException $e) {
        }

        $this->assertTrue(true);
    }

    public function test_ifz_with_leading_zeros_do_not_throw_exception(): void
    {
        try {
            new VatId("01234567S", VatId::VAT_ID_TYPE_IFZ);
            new VatId("00000567S", VatId::VAT_ID_TYPE_IFZ);
            $this->assertTrue(true);
        } catch (InvalidVatIdException $e) {
            $this->fail();
        }
    }
}
