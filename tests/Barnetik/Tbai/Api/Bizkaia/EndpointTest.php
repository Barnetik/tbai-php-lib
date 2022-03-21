<?php

namespace Test\Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\Bizkaia\Endpoint;
use Barnetik\Tbai\SubmitInvoiceFile;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class EndpointTest extends TestCase
{
    const DEFAULT_TERRITORY = SubmitInvoiceFile::TERRITORY_GIPUZKOA;
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_sent_FacturasEmitidasConSGAltaPeticion_xml_is_valid(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];
        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_BIZKAIA);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->createSubmitInvoiceRequest($ticketbai)->data();
        $dom = new DOMDocument();
        $dom->loadXML(gzdecode($response));
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/../../__files/specs/Api/Bizkaia/petition-schemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd'));
    }

    public function test_TicketBai_can_be_sent(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];
        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_BIZKAIA);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Api(SubmitInvoiceFile::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
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
}
