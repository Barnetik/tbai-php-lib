<?php

namespace Test\Barnetik\Tbai\ValueObject;

use Barnetik\Tbai\Exception\InvalidVatIdException;
use Barnetik\Tbai\ValueObject\VatId;
use Test\Barnetik\TestCase;

class VatIdTest extends TestCase
{
    public function test_ifz_with_wrong_format_throw_exception(): void
    {
        $this->expectException(InvalidVatIdException::class);
        new VatId("1234567S", "wrongType");
    }

    public function test_ifz_with_leading_zero_does_not_throw_exception(): void
    {
        $this->expectNotToPerformAssertions();
        new VatId("01234567S", VatId::VAT_ID_TYPE_IFZ);
    }

    public function test_ifz_with_multiple_leading_zeros_does_not_throw_exception(): void
    {
        $this->expectNotToPerformAssertions();
        new VatId("00000567S", VatId::VAT_ID_TYPE_IFZ);
    }
}
