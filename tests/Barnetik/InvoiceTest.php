<?php
namespace Barnetik;

use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\InvoiceLine;
use PHPUnit\Framework\TestCase;


class InvoiceTest extends TestCase
{

    public function testInvoiceCanBeCreated()
    {
        $invoiceNumber = 'TEST-INVOICE-000001';
        $description = 'This is a test invoice';
        $totalBefore = 55.78;
        $totalAfter = 89.00;
        $taxType = Invoice::TAX_TYPE_10;
        $invoice = new Invoice($invoiceNumber, $description, $totalBefore, $totalAfter, $taxType);

        $this->assertEquals($invoiceNumber, $invoice->invoiceNumber());
        $this->assertEquals($description, $invoice->description());
        $this->assertEquals($totalBefore, $invoice->beforeTaxTotal());
        $this->assertEquals($totalAfter, $invoice->afterTaxTotal());
        $this->assertEquals($taxType, $invoice->taxType());
    }

    public function testInvoiceCanHaveLines()
    {
        $invoiceNumber = 'TEST-INVOICE-000001';
        $description = 'This is a test invoice';
        $totalBefore = 55.78;
        $totalAfter = 89.00;
        $taxType = Invoice::TAX_TYPE_10;
        $invoice = new Invoice($invoiceNumber, $description, $totalBefore, $totalAfter, $taxType);
        $firstLine = new InvoiceLine('This is the first line', 55.78, 89.00, InvoiceLine::TAX_TYPE_10);
        $invoice->addLine($firstLine);

        $this->assertIsArray($invoice->lines());
        $this->assertCount(1, $invoice->lines());
        $this->assertEquals('This is the first line', $invoice->lines()[0]->description());
    }
}