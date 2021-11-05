<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\InvoiceLine;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{

    public function testInvoiceCanBeCreated(): void
    {
        $invoiceNumber = 'TEST-INVOICE-000001';
        $description = 'This is a test invoice';
        $totalBefore = 55.78;
        $totalAfter = 89.00;
        $taxType = Invoice::TAX_TYPE_10;
        $invoice = new Invoice($invoiceNumber, $description, (string) $totalBefore, (string) $totalAfter, $taxType);

        $this->assertEquals($invoiceNumber, $invoice->invoiceNumber());
        $this->assertEquals($description, $invoice->description());
        $this->assertEquals($totalBefore, $invoice->beforeTaxTotal());
        $this->assertEquals($totalAfter, $invoice->afterTaxTotal());
        $this->assertEquals($taxType, $invoice->taxType());
    }

    public function testInvoiceCanHaveLines(): void
    {
        $invoiceNumber = 'TEST-INVOICE-000001';
        $description = 'This is a test invoice';
        $totalBefore = 55.78;
        $totalAfter = 89.00;
        $taxType = Invoice::TAX_TYPE_10;
        $invoice = new Invoice($invoiceNumber, $description, (string) $totalBefore, (string) $totalAfter, $taxType);
        $firstLine = new InvoiceLine('This is the first line', (string) $totalBefore, (string) $totalAfter, InvoiceLine::TAX_TYPE_10);
        $invoice->addLine($firstLine);

        $this->assertIsArray($invoice->lines());
        $this->assertCount(1, $invoice->lines());
        $this->assertEquals('This is the first line', $invoice->lines()[0]->description());
    }
}
