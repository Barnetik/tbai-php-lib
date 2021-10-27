<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\InvoiceLine;
use PHPUnit\Framework\TestCase;

class InvoiceLineTest extends TestCase
{

    public function testInvoiceLineCanBeCreated()
    {
        $description = 'This is a test line';
        $priceBefore = 55.78;
        $priceAfter = 89.00;
        $taxType = InvoiceLine::TAX_TYPE_10;
        $invoice = new InvoiceLine($description, $priceBefore, $priceAfter, $taxType);

        $this->assertEquals($description, $invoice->description());
        $this->assertEquals($priceBefore, $invoice->beforeTaxPrice());
        $this->assertEquals($priceAfter, $invoice->afterTaxPrice());
        $this->assertEquals($taxType, $invoice->taxType());
    }
}
