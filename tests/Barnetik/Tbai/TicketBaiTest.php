<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;
use PHPUnit\Framework\TestCase;

class TicketBaiTest extends TestCase
{
    public function test_TicketBai_can_be_created(): void
    {
        $subject = $this->getSubject();
        $fingerprint = $this->getFingerprint();

        $header = Header::create('0000001', date('d-m-Y'), date('H:i:s'), 'TEST-SERIE-');
        $data = new Data('test-description', '12,34', [Data::VAT_REGIME_01]);
        $breakdown = new Breakdown();
        $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem('12,34', NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
        $invoice = new Invoice($header, $data, $breakdown);

        $ticketbai = new TicketBai(
            $subject,
            $invoice,
            $fingerprint
        );

        echo $ticketbai;

        $this->assertTrue(true);
    }

    private function getSubject(): Subject
    {
        $emitter = new Emitter('11111111H', 'Emitter Name');
        $recipient = Recipient::createNationalRecipient('00000000T', 'Client Name');
        return new Subject($emitter, $recipient, Subject::EMITTED_BY_EMITTER);
    }

    private function getFingerprint(): Fingerprint
    {
        $vendor = new Vendor('testLicenseKey', 'barnetik');
        return new Fingerprint($vendor);
    }
}
