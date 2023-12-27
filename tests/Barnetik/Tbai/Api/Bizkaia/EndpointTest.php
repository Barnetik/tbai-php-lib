<?php

namespace Test\Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Bizkaia\Endpoint;
use Barnetik\Tbai\Api\Bizkaia\LroeResultInterface;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class EndpointTest extends TestCase
{
    const SUBMIT_RETRIES = 3;
    const SUBMIT_RETRY_DELAY = 3;
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_BIZKAIA;

    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_sent_FacturasEmitidasConSGAltaPeticion_xml_is_valid(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_240'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_240'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];
        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_BIZKAIA);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->createSubmitInvoiceRequest($ticketbai)->data();
        $dom = new DOMDocument();
        $dom->loadXML(gzdecode($response));
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/../../__files/specs/Api/Bizkaia/petition-schemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd'));
    }

    public function test_TicketBai_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_intracomunitary_with_vat(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-intracomunitary-eu-vat-id-recipient.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_exports(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-export.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_ISP(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-sample-isp.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_using_PEM(): void
    {
        $certFile = $_ENV['TBAI_TEST_SINGLE_PEM_PATH'];
        $certPassword = $_ENV['TBAI_TEST_SINGLE_PEM_PASSWORD'];
        $privateKey = PrivateKey::pem($certFile, $certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_with_operation_date_is_delivered(): void
    {

        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_240'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_240'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];

        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithOperationDate($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_BIZKAIA);
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_selfEmployed(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_selfEmployed_created_from_json(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForSelfEmployedFromJson();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_selfEmployed_with_multiple_incomeTaxEpigraphs(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed(true);
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "eus-bizkaia-n3-mensaje-respuesta: " . $response->header('eus-bizkaia-n3-mensaje-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_multiVat_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_canceled(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $signedFilename = $signedFilename . '-cancel.xml';
        $ticketbaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_canceled_for_selfEmployed(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $signedFilename = $signedFilename . '-cancel.xml';
        $ticketbaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createBizkaiaTicketBaiRectification($ticketbai);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbaiRectification, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified_for_selfEmployed(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createBizkaiaTicketBaiRectificationForSelfEmployed($ticketbai);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbaiRectification, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_can_be_signed_and_restored_for_async_send(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $jsonString = json_encode($ticketbai->toArray());
        $json = json_decode($jsonString, true);

        $restoredTbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $restoredTbai->setSignedXmlPath($signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($restoredTbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isDelivered());
    }


    public function test_json_data_is_correct_on_sample_with_multiple_same_vat(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(__DIR__ . '/../../__files/tbai-sample-with-multiple-same-vat.json');
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_json_data_is_correct_on_json_file_sample(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(__DIR__ . '/../../__files/tbai-sample.json');
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $ticketbai->issuerVatId() . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "eus-bizkaia-n3-tipo-respuesta: " . $response->header('eus-bizkaia-n3-tipo-respuesta') . "\n";
            echo "eus-bizkaia-n3-identificativo: " . $response->header('eus-bizkaia-n3-identificativo') . "\n";
            echo "eus-bizkaia-n3-codigo-respuesta: " . $response->header('eus-bizkaia-n3-codigo-respuesta') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(Api::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertFalse($response->hasErrorData());
        $this->assertTrue($response->isCorrect());
    }

    public function test_sending_duplicated_invoice_number_returns_duplicated_error(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $responseFile . '-duplicated';
        file_put_contents($responseFile, $response->content());

        $registryError = $response->errorDataRegistry();
        $this->assertTrue($response->hasErrorData());
        $this->assertArrayHasKey('errorCode', $registryError[0]);
        $this->assertArrayHasKey('errorMessage', $registryError[0]);
        $this->assertEquals('B4_2000003', $registryError[0]['errorCode']);
        $this->assertEquals('Registro duplicado.', $registryError[0]['errorMessage']['es']);
        $this->assertEquals('Erregistro bikoiztua.', $registryError[0]['errorMessage']['eu']);

        $this->assertFalse($response->isDelivered());
        $this->assertFalse($response->isCorrect());
    }

    public function test_wrong_issuer_is_marked_as_incorrect_response(): void
    {
        $certFile = $_ENV['TBAI_BIZKAIA_P12_PATH'];
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $json = json_decode(file_get_contents(__DIR__ . '/../../__files/tbai-sample-with-multiple-same-vat.json'), true);
        $json['invoice']['header']['invoiceNumber'] = (string)time();
        sleep(1);

        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $this->assertFalse($response->isCorrect());
        $this->assertFalse($response->isDelivered());
    }
}
