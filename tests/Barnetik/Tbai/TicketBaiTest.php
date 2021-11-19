<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\VatDetail;
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

        $header = Header::create('0000002', date('d-m-Y'), date('H:i:s'), 'TEST-SERIE-');
        $data = new Data('test-description', '12.34', [Data::VAT_REGIME_01]);
        $breakdown = new Breakdown();
        $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem('12.34', NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
        $breakdown->addNationalSubjectExemptBreakdownItem(new NationalSubjectExemptBreakdownItem('56.78', NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_23));

        $notExemptBreakdown = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1);
        $notExemptBreakdown->addVatDetail(new VatDetail('98.76', '4.12', '3.01'));
        $breakdown->addNationalSubjectNotExemptBreakdownItem($notExemptBreakdown);

        // (new NationalNotSubjectBreakdownItem('12.34', NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
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
        $vendor = new Vendor('testLicenseKey', 'F95780987');
        $previousInvoice = new PreviousInvoice('0000002', '02-12-2020', 'abcdefgkauskjsa', 'TEST-SERIE-');
        return new Fingerprint($vendor, $previousInvoice);
    }
}
