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
use lyquidity\xmldsig\XAdES;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
  public function test_TicketBai_can_be_sent_to_bizkaia_endpoint(): void
  {
    $ticketbai = $this->getTicketBai();
    $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
    rename($signedFilename, $signedFilename . '.xml');
    $signedFilename = $signedFilename . '.xml';
    $certFile = $_ENV['TBAI_P12_PATH'];
    $certPassword = $_ENV['TBAI_PRIVATE_KEY'];

    $ticketbai->sign($certFile, $certPassword, $signedFilename);

    $endpoint = new Api(Api::ENDPOINT_BIZKAIA, true, true);

    $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

    $responseFile = tempnam(__DIR__ . '/__files/responses', 'response-');
    file_put_contents($responseFile, $response->content());

    if (!$response->isCorrect()) {
      echo "\n";
      echo "IFZ: " . $_ENV['TBAI_ISSUER_NIF'] . "\n";
      echo "Data: " . date('Y-m-d H:i:s') . "\n";
      echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
      echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
      echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
      echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
      echo "Bidalitako fitxategia: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
      echo "Sinatutako fitxategia: " . basename($signedFilename) . "\n";
      echo "Erantzuna: " . basename($responseFile) . "\n";
    }

    $this->assertTrue($response->isCorrect());
  }

  public function test_TicketBai_sent_xml_is_valid(): void
  {
    $ticketbai = $this->getTicketBai();
    $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
    rename($signedFilename, $signedFilename . '.xml');
    $signedFilename = $signedFilename . '.xml';
    $certFile = $_ENV['TBAI_P12_PATH'];
    $certPassword = $_ENV['TBAI_PRIVATE_KEY'];

    $ticketbai->sign($certFile, $certPassword, $signedFilename);

    $endpoint = new Api(Api::ENDPOINT_BIZKAIA, true, true);

    $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

    $dom = new DOMDocument();
    $dom->loadXML(gzdecode(file_get_contents($endpoint->debugData(Api::DEBUG_SENT_FILE))));
    $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/Api/Bizkaia/petition-schemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd'));
  }

  private function getTicketBai(): TicketBai
  {
    $subject = $this->getSubject();
    $fingerprint = $this->getFingerprint();

    $header = Header::create((string)time(), new Date(date('d-m-Y')), new Time(date('H:i:s')), 'TESTSERIE');
    sleep(1); // Avoid same invoice number as time is used for generation
    $data = new Data('test-description', new Ammount('12.34'), [Data::VAT_REGIME_01]);
    $breakdown = new Breakdown();
    $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem(new Ammount('12.34'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
    $breakdown->addNationalSubjectExemptBreakdownItem(new NationalSubjectExemptBreakdownItem(new Ammount('56.78'), NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_23));

    $vatDetail = new VatDetail(new Ammount('98.76'), new Ammount('21.00'), new Ammount('20.74'));
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
    $vendor = new Vendor($_ENV['TBAI_APP_LICENSE'], $_ENV['TBAI_APP_DEVELOPER_NIF'], $_ENV['TBAI_APP_NAME'], $_ENV['TBAI_APP_VERSION']);
    $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
    return new Fingerprint($vendor, $previousInvoice);
  }
}
