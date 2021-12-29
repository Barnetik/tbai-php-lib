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
use PHPUnit\Framework\TestCase;

class LroeTest extends TestCase
{
  public function test_TicketBai_can_be_sent_to_lroe(): void
  {
    $ticketbai = $this->getTicketBai();
    $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
    rename($filename, $filename . '.xml');
    $filename = $filename . '.xml';
    $certFile = $_ENV['TBAI_P12_PATH'];
    $certPassword = $_ENV['TBAI_PRIVATE_KEY'];

    $ticketbai->sign($certFile, $certPassword, $filename);

    $lroe = new LROE(LROE::ENDPOINT_BIZKAIA);
    $response = $lroe->submitInvoice($ticketbai, $certFile, $certPassword);
    $this->assertTrue($response->isCorrect());
  }

  private function getTicketBai(): TicketBai
  {
    $subject = $this->getMultipleRecipientSubject();
    $fingerprint = $this->getFingerprint();

    $header = Header::create('0000002', new Date('02-11-2021'), new Time('11:12:10'), 'TESTSERIE');
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
      $fingerprint
    );
  }

  private function getSubject(): Subject
  {
    $nif = $_ENV['TBAI_ISSUER_NIF'];
    $name = $_ENV['TBAI_ISSUER_NAME'];
    $issuer = new Issuer(new VatId($nif), $name);
    $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Client Name');
    return new Subject($issuer, $recipient, Subject::ISSUED_BY_ISSUER);
  }

  private function getMultipleRecipientSubject(): Subject
  {
    $subject = $this->getSubject();
    $subject->addRecipient(Recipient::createGenericRecipient(new VatId('X0000000I', VatId::VAT_ID_TYPE_RESIDENCE_CERTIFICATE), 'Client Name 2', '48270', 'IE'));
    return $subject;
  }

  private function getFingerprint(): Fingerprint
  {
    $vendor = new Vendor('testLicenseKey', 'F95780987');
    $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
    return new Fingerprint($vendor, $previousInvoice);
  }
}
