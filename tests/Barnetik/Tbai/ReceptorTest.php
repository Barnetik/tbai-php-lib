<?php

namespace Barnetik\Tbai;

use PHPUnit\Framework\TestCase;

class ReceptorTest extends TestCase
{
    public function testNationalReceptorTypeIsIfz()
    {
        $receptor = Receptor::createNationalReceptor('11111111H', 'Test business', 48270);

        $this->assertEquals(Receptor::TAX_ID_TYPE_IFZ, $receptor->taxIdType());
    }
}
