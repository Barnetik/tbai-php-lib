<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\InvoiceLine;
use PHPUnit\Framework\TestCase;

class InvoiceLineTest extends TestCase
{

    public function test_invoice_line_can_be_created(): void
    {
        $description = 'This is a test line';
        $priceBefore = 55.78;
        $priceAfter = 89.00;
        $taxType = InvoiceLine::TAX_TYPE_10;
        $invoice = new InvoiceLine($description, (string) $priceBefore, (string) $priceAfter, $taxType);

        $this->assertEquals($description, $invoice->description());
        $this->assertEquals($priceBefore, $invoice->beforeTaxPrice());
        $this->assertEquals($priceAfter, $invoice->afterTaxPrice());
        $this->assertEquals($taxType, $invoice->taxType());
    }
}
