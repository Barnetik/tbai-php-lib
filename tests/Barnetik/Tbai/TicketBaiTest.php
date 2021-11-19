<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class TicketBaiTest extends TestCase
{
    public function test_TicketBai_can_be_created(): void
    {
        // $subject = $this->getSubject();
        $subject = $this->getMultipleRecipientSubject();
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

        $dom = $ticketbai->toDom();
        $dom->formatOutput = true;

        echo $dom->saveXml();
        $dom->schemaValidate(__DIR__ . '/__files/ticketBaiV1-2-no-signature.xsd');



        $this->assertTrue(true);
    }

    private function getSubject(): Subject
    {
        $emitter = new Emitter('11111111H', 'Emitter Name');
        $recipient = Recipient::createNationalRecipient('00000000T', 'Client Name');
        return new Subject($emitter, $recipient, Subject::EMITTED_BY_EMITTER);
    }

    private function getMultipleRecipientSubject(): Subject
    {
        $subject = $this->getSubject();
        $subject->addRecipient(Recipient::createGenericRecipient('X0000000I', 'Client Name 2', '48270', Recipient::VAT_ID_TYPE_RESIDENCE_CERTIFICATE, 'IE'));
        return $subject;
    }

    private function getFingerprint(): Fingerprint
    {
        $vendor = new Vendor('testLicenseKey', 'barnetik');
        return new Fingerprint($vendor);
    }
}