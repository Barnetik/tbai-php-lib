<?php

namespace Test\Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Araba\Endpoint;
use Barnetik\Tbai\Interfaces\TbaiSignable;
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
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_multiVat_is_delivered(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_delivered_for_ISP(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBaiFromJson(
            __DIR__ . '/../../__files/tbai-sample-isp.json'
        );
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }


    public function test_TicketBai_is_canceled(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);
        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $signedFilename = $this->signFile($ticketbaiCancel, $privateKey, $certPassword, '-cancel.xml');
        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified_by_substitution(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createArabaTicketBaiRectificationBySubstitution($ticketbai);
        $signedFilename = $this->signFile($ticketbaiRectification, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbaiRectification, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);
        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified_by_difference(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createArabaTicketBaiRectificationByDifference($ticketbai);
        $signedFilename = $this->signFile($ticketbaiRectification, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbaiRectification, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);
        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_can_be_signed_and_restored_for_async_send(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $jsonString = json_encode($ticketbai->toArray());
        $json = json_decode($jsonString, true);

        $restoredTbai = TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), $json);
        $restoredTbai->setSignedXmlPath($signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($restoredTbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);
        $this->assertTrue($response->isDelivered());
    }

    
    public function test_Zuzendu_modify_is_delivered(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaWrongTicketBai();

        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzendu = $this->ticketBaiMother->createZuzenduToModifyWrongTicketBai($ticketbai);

        $response = $endpoint->submitZuzendu($zuzendu, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_ZuzenduCancel_is_delivered(): void
    {
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);
        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        sleep(1);

        $wrongTicketBaiCancel = $this->ticketBaiMother->createArabaTicketBaiCancel();
        $signedFilename = $this->signFile($wrongTicketBaiCancel, $privateKey, $certPassword, 'wrong-cancel.xml');
        $response = $endpoint->cancelInvoice($wrongTicketBaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzenduCancel = $this->ticketBaiMother->createZuzenduCancelForTicketBai($wrongTicketBaiCancel, $ticketbai);
        $response = $endpoint->cancelZuzendu($zuzenduCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

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
        [$privateKey, $certPassword] = $this->getArabaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $certPassword);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $response, $signedFilename, $responseFile);

        $this->assertIsArray($response->headers());
    }

    private function getArabaP12Credentials(): array
    {
        return [
            PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']),
            $_ENV['TBAI_ARABA_PRIVATE_KEY'],
        ];
    }

    private function signFile(TbaiSignable $ticketbai, PrivateKey $privateKey, string $password, string $suffix = ''): string
    {
        $filename = tempnam(__DIR__ . '/../../__files/signedXmls', date('YmdHis') . '-signed-');
        rename($filename, $filename . '.xml');
        $filename = $filename . '.xml';
        if ($suffix) {
            $filename = $filename . $suffix;
        }
        $ticketbai->sign($privateKey, $password, $filename);
        return $filename;
    }

    private function saveResponseToFile($response): string
    {
        $filename = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($filename);
        return $filename;
    }

    private function debugResponseWithFile($endpoint, $response, string $signedFile, string $responseFile): void
    {
        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_ARABA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFile) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
    }

}
