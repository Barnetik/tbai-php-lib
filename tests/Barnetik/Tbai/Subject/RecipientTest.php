<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\ValueObject\VatId;
use PHPUnit\Framework\TestCase;

class RecipientTest extends TestCase
{
    public function test_national_recipient_id_type_is_ifz(): void
    {
        $receptor = Recipient::createNationalRecipient(new VatId('11111111H'), 'Test business', (string) 48270);

        $this->assertEquals(VatId::VAT_ID_TYPE_IFZ, $receptor->vatIdType());
        $this->assertEquals('Test business', $receptor->name());
        $this->assertEquals('ES', $receptor->countryCode());
        $this->assertEquals(48270, $receptor->postalCode());
        $this->assertEquals('11111111H', $receptor->vatId());
    }

    public function test_foreign_recipient_can_be_created(): void
    {
        $receptor = Recipient::createGenericRecipient(new VatId('abcdefghijkl', VatId::VAT_ID_TYPE_PASSPORT), 'Test foreign business', (string) 48260, 'UK');

        $this->assertEquals(VatId::VAT_ID_TYPE_PASSPORT, $receptor->vatIdType());
        $this->assertEquals('Test foreign business', $receptor->name());
        $this->assertEquals('UK', $receptor->countryCode());
        $this->assertEquals(48260, $receptor->postalCode());
        $this->assertEquals('abcdefghijkl', $receptor->vatId());
    }
}
