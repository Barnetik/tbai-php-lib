<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Api\AbstractTerritory;
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

class ApiTest extends TestCase
{
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_GIPUZKOA;

    public function test_TicketBai_can_be_sent_to_bizkaia_endpoint(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];

        $ticketbai = $this->getTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion);
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Api(Api::ENDPOINT_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

        $responseFile = tempnam(__DIR__ . '/__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "IFZ: " . $_ENV['TBAI_BIZKAIA_ISSUER_NIF'] . "\n";
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

    public function test_TicketBai_can_be_sent_to_gipuzkoa_endpoint(): void
    {
        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];

        $ticketbai = $this->getTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion);
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Api(Api::ENDPOINT_GIPUZKOA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

        $responseFile = tempnam(__DIR__ . '/__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "IFZ: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Data: " . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            // echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            // echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            // echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Bidalitako fitxategia: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Sinatutako fitxategia: " . basename($signedFilename) . "\n";
            echo "Jasotako errore printzipala: " . $response->mainErrorMessage() . "\n";
            echo "Erantzuna: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_sent_xml_is_valid(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];

        $ticketbai = $this->getTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion);
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Api(Api::ENDPOINT_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

        $dom = new DOMDocument();
        $dom->loadXML(gzdecode(file_get_contents($endpoint->debugData(Api::DEBUG_SENT_FILE))));
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/Api/Bizkaia/petition-schemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd'));
    }

    private function getTicketBai(string $nif, string $issuer, string $license, string $developer, string $appName, string $appVersion): TicketBai
    {
        $subject = $this->getSubject($nif, $issuer);
        $fingerprint = $this->getFingerprint($license, $developer, $appName, $appVersion);

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
            $fingerprint,
            self::DEFAULT_TERRITORY
        );
    }

    private function getSubject(string $nif, string $name): Subject
    {
        $issuer = new Issuer(new VatId($nif), $name);
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Client Name', '48270', 'Markina-Xemein');
        return new Subject($issuer, $recipient, Subject::ISSUED_BY_ISSUER);
    }

    private function getFingerprint(string $license, string $developer, string $appName, string $appVersion): Fingerprint
    {
        $vendor = new Vendor($license, $developer, $appName, $appVersion);
        // $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
        // return new Fingerprint($vendor, $previousInvoice);
        // $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
        return new Fingerprint($vendor);
    }
}
