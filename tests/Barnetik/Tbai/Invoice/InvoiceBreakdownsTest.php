<?php
namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class InvoiceBreakdownsTest extends TestCase
{
    public function test_NotSubjectBreakdownItem_can_be_created(): void
    {
        $notSubjectItem = new NationalNotSubjectBreakdownItem('12,25', NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_RL);
        $this->assertEquals('12,25', $notSubjectItem->ammount());
    }

    public function test_NotSubjectBreakdownItems_are_limited(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $invoiceBreakdown = new InvoiceBreakdown();
        $notSubjectItem = new NationalNotSubjectBreakdownItem('12,25', NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_RL);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
    }
}