<?php

namespace Test\Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Araba\Endpoint;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Test\Barnetik\TestCase;

class EndpointTest extends TestCase
{
    const SUBMIT_RETRIES = 3;
    const SUBMIT_RETRY_DELAY = 3;
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_ARABA;

    public function test_TicketBai_is_delivered(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_multiVat_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_canceled(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);
        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $signedFilename = $signedFilename . '-cancel.xml';
        $ticketbaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
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
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createArabaTicketBaiRectification($ticketbai);
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
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
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
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $jsonString = json_encode($ticketbai->toArray());
        $json = json_decode($jsonString, true);

        $restoredTbai = TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), $json);
        $restoredTbai->setSignedXmlPath($signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($restoredTbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isDelivered());
    }

    public function test_Zuzendu_modify_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createArabaWrongTicketBai();

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzendu = $this->ticketBaiMother->createZuzenduToModifyWrongTicketBai($ticketbai);

        $response = $endpoint->submitZuzendu($zuzendu, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_ZuzenduCancel_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);
        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        sleep(1);

        $wrongTicketBaiCancel = $this->ticketBaiMother->createArabaTicketBaiCancel();
        $signedFilename = $signedFilename . 'wrong-cancel.xml';
        $wrongTicketBaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($wrongTicketBaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzenduCancel = $this->ticketBaiMother->createZuzenduCancelForTicketBai($wrongTicketBaiCancel, $ticketbai);
        $response = $endpoint->cancelZuzendu($zuzenduCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_response_headers_can_be_retrieved(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertIsArray($response->headers());
    }

}
