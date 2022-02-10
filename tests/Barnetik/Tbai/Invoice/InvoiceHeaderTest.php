<?php

namespace Test\Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidDateException;
use Barnetik\Tbai\Exception\InvalidTimeException;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use PHPUnit\Framework\TestCase;

class InvoiceHeaderTest extends TestCase
{
    public function test_invoice_header_can_be_created(): void
    {
        $invoiceHeader = Header::create('00001', new Date('02-09-2021'), new Time('21:21:21'), 'SERIE');
        $this->assertIsObject($invoiceHeader);
        $this->assertEquals('SERIE', $invoiceHeader->series());
        $this->assertEquals('00001', $invoiceHeader->invoiceNumber());
        $this->assertNotEquals('1', $invoiceHeader->invoiceNumber());
        $this->assertEquals('02-09-2021', $invoiceHeader->expeditionDate());
        $this->assertEquals('21:21:21', $invoiceHeader->expeditionTime());
    }

    public function test_wrong_date_format_throws_exception(): void
    {
        $this->expectException(InvalidDateException::class);
        Header::create('00001', new Date('2021-09-02'), new Time('21:21:21'), 'SERIE');
    }

    public function test_wrong_time_format_throws_exception(): void
    {
        $this->expectException(InvalidTimeException::class);
        Header::create('00001', new Date('02-09-2021'), new Time('25:21'), 'SERIE');
    }
}
