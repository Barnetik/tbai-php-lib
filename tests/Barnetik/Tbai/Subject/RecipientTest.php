<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Subject\Recipient;
use PHPUnit\Framework\TestCase;

class RecipientTest extends TestCase
{
    public function testNationalReceptorTypeIsIfz(): void
    {
        $receptor = Recipient::createNationalRecipient('11111111H', 'Test business', (string) 48270);

        $this->assertEquals(Recipient::TAX_ID_TYPE_IFZ, $receptor->taxIdType());
        $this->assertEquals('Test business', $receptor->name());
        $this->assertEquals('ES', $receptor->countryCode());
        $this->assertEquals(48270, $receptor->postalCode());
        $this->assertEquals('11111111H', $receptor->taxId());
    }

    public function testForeignReceptorIsCreated(): void
    {
        $receptor = Recipient::createGenericRecipient('abcdefghijkl', 'Test foreign business', (string) 48260, Recipient::TAX_ID_TYPE_PASSPORT, 'UK');

        $this->assertEquals(Recipient::TAX_ID_TYPE_PASSPORT, $receptor->taxIdType());
        $this->assertEquals('Test foreign business', $receptor->name());
        $this->assertEquals('UK', $receptor->countryCode());
        $this->assertEquals(48260, $receptor->postalCode());
        $this->assertEquals('abcdefghijkl', $receptor->taxId());
    }
}
