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
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\Subject\Recipient;
use DOMDocument;
use Exception;
use lyquidity\xmldsig\XAdES;
use PHPUnit\Framework\TestCase;

class TicketBaiTest extends TestCase
{
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_BIZKAIA;

    public function test_unsigned_TicketBai_validates_schema(): void
    {
        $ticketbai = $this->getTicketBai();
        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_PFX_key(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $ticketbai->sign($_ENV['TBAI_P12_PATH'], $_ENV['TBAI_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));

        // $qr = new Qr($ticketbai);
        // var_dump($qr->ticketbaiIdentifier());
        // var_dump($qr->qrUrl());
    }

    public function test_TicketBai_signed_file_is_valid(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $ticketbai->sign($_ENV['TBAI_P12_PATH'], $_ENV['TBAI_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        try {
            XAdES::verifyDocument(
                $filename
            );
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    private function getTicketBai(): TicketBai
    {
        $subject = $this->getMultipleRecipientSubject();
        $fingerprint = $this->getFingerprint();

        $header = Header::create((string)time(), new Date(date('d-m-Y')), new Time(date('H:i:s')), 'TESTSERIE');
        sleep(1); // Avoid same invoice number as time is used for generation
        $data = new Data('test-description', new Ammount('12.34'), [Data::VAT_REGIME_01]);
        $breakdown = new Breakdown();
        $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem(new Ammount('12.34'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
        $breakdown->addNationalSubjectExemptBreakdownItem(new NationalSubjectExemptBreakdownItem(new Ammount('56.78'), NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_23));

        $vatDetail = new VatDetail(new Ammount('98.76'), new Ammount('4.12'), new Ammount('3.01'));
        $notExemptBreakdown = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
        $breakdown->addNationalSubjectNotExemptBreakdownItem($notExemptBreakdown);

        $invoice = new Invoice($header, $data, $breakdown);

        return new TicketBai(
            $subject,
            $invoice,
            $fingerprint,
            self::DEFAULT_TERRITORY
        );
    }

    private function getSubject(): Subject
    {
        $issuer = new Issuer(new VatId('11111111H'), 'Emitter Name');
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Client Name', '48270', 'Markina-Xemein');
        return new Subject($issuer, $recipient, Subject::ISSUED_BY_ISSUER);
    }

    private function getMultipleRecipientSubject(): Subject
    {
        $subject = $this->getSubject();
        $subject->addRecipient(Recipient::createGenericRecipient(new VatId('X0000000I', VatId::VAT_ID_TYPE_RESIDENCE_CERTIFICATE), 'Client Name 2', '48270', 'Ballycastle', 'IE'));
        return $subject;
    }

    private function getFingerprint(): Fingerprint
    {
        $vendor = new Vendor($_ENV['TBAI_APP_LICENSE'], $_ENV['TBAI_APP_DEVELOPER_NIF'], $_ENV['TBAI_APP_NAME'], $_ENV['TBAI_APP_VERSION']);
        $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
        return new Fingerprint($vendor, $previousInvoice);
    }
}
