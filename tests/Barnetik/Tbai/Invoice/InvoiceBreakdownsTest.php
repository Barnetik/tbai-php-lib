<?php

namespace Test\Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\VatDetail;
use Barnetik\Tbai\ValueObject\Amount;
use OutOfBoundsException;
use Test\Barnetik\TestCase;

class InvoiceBreakdownsTest extends TestCase
{
    public function test_NotSubjectBreakdownItem_can_be_created(): void
    {
        $notSubjectItem = new NationalNotSubjectBreakdownItem(new Amount('12.25'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_RL);
        $this->assertEquals('12.25', $notSubjectItem->amount());
    }

    public function test_NotSubjectBreakdownItems_are_limited(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $invoiceBreakdown = new Breakdown();
        $notSubjectItem = new NationalNotSubjectBreakdownItem(new Amount('12.25'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_RL);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
        $invoiceBreakdown->addNationalNotSubjectBreakdownItem($notSubjectItem);
    }

    public function test_SubjectExemptBreakdownItems_are_limited(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $invoiceBreakdown = new Breakdown();
        $subjectExemptItem = new NationalSubjectExemptBreakdownItem(new Amount('12.12'), NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_20);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectExemptBreakdownItem($subjectExemptItem);
    }

    public function test_SubjectNotExemptBreakdownItems_are_limited(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $invoiceBreakdown = new Breakdown();
        $vatDetail = new VatDetail(new Amount('12.12'), new Amount('34.56'), new Amount('78.90'));
        $subjectExemptItem = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
        $invoiceBreakdown->addNationalSubjectNotExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectNotExemptBreakdownItem($subjectExemptItem);
        $invoiceBreakdown->addNationalSubjectNotExemptBreakdownItem($subjectExemptItem);
    }
}
