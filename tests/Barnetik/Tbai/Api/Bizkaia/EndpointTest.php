<?php

namespace Test\Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\AbstractTicketBai;
use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Bizkaia\Endpoint;
use Barnetik\Tbai\Interfaces\TbaiSignable;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use DOMDocument;
use Test\Barnetik\TestCase;

class EndpointTest extends TestCase
{
    const SUBMIT_RETRIES = 3;
    const SUBMIT_RETRY_DELAY = 3;
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_BIZKAIA;

    public function test_sent_FacturasEmitidasConSGAltaPeticion_xml_is_valid(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();
        
        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->createSubmitInvoiceRequest($ticketbai)->data();
        
        $dom = new DOMDocument();
        $dom->loadXML(gzdecode($response));
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/../../__files/specs/Api/Bizkaia/petition-schemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd'));
    }

    public function test_TicketBai_is_delivered(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_intracomunitary_with_vat(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-intracomunitary-eu-vat-id-recipient.json'
        );
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_exports(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-export.json'
        );
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_rebu(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-rebu.json'
        );
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_ISP(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(
            __DIR__ . '/../../__files/tbai-sample-isp.json'
        );
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_using_PEM(): void
    {
        [$privateKey, $password] = $this->getBizkaiaPemCredentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_with_operation_date_is_delivered(): void
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF_240'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME_240'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion = $_ENV['TBAI_BIZKAIA_APP_VERSION'];

        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithOperationDate($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_BIZKAIA);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_selfEmployed(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_selfEmployed_created_from_json(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForSelfEmployedFromJson();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_delivered_for_selfEmployed_with_multiple_incomeTaxEpigraphs(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed(true);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_multiVat_is_delivered(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_TicketBai_is_canceled(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $cancelFilename = $this->signFile($ticketbaiCancel, $privateKey, $password, '-cancel.xml');

        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);

        $this->debugResponseWithFile($endpoint, $ticketbaiCancel, $response, $cancelFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_canceled_for_selfEmployed(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $cancelFilename = $this->signFile($ticketbaiCancel, $privateKey, $password, '-cancel.xml');

        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);

        $this->debugResponseWithFile($endpoint, $ticketbaiCancel, $response, $cancelFilename, $responseFile);

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createBizkaiaTicketBaiRectificationBySubstitution($ticketbai);
        $rectFilename = $this->signFile($ticketbaiRectification, $privateKey, $password);

        $this->assertSubmissionSuccessful($endpoint, $ticketbaiRectification, $privateKey, $password, $rectFilename);
    }

    public function test_TicketBai_is_rectified_by_substitution_for_selfEmployed(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createBizkaiaTicketBaiRectificationBySubstitutionForSelfEmployed($ticketbai);
        $rectFilename = $this->signFile($ticketbaiRectification, $privateKey, $password);

        $this->assertSubmissionSuccessful($endpoint, $ticketbaiRectification, $privateKey, $password, $rectFilename);
    }

    public function test_TicketBai_is_rectified_by_difference_for_selfEmployed(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createBizkaiaTicketBaiRectificationByDifferenceForSelfEmployed($ticketbai);
        $rectFilename = $this->signFile($ticketbaiRectification, $privateKey, $password);

        $endpoint = new Endpoint(true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbaiRectification, $privateKey, $password, $rectFilename);
    }

    public function test_TicketBai_can_be_signed_and_restored_for_async_send(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiSelfEmployed();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $jsonString = json_encode($ticketbai->toArray());
        $json = json_decode($jsonString, true);

        $restoredTbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $restoredTbai->setSignedXmlPath($signedFilename);

        $endpoint = new Endpoint(true, true);

        $this->assertSubmissionSuccessful($endpoint, $restoredTbai, $privateKey, $password, $signedFilename);
    }


    public function test_json_data_is_correct_on_sample_with_multiple_same_vat(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(__DIR__ . '/../../__files/tbai-sample-with-multiple-same-vat.json');
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $this->assertSubmissionSuccessful($endpoint, $ticketbai, $privateKey, $password, $signedFilename);
    }

    public function test_json_data_is_correct_on_json_file_sample(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(__DIR__ . '/../../__files/tbai-sample.json');
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $ticketbai, $response, $signedFilename, $responseFile);

        $this->assertFalse($response->hasErrorData());
    }

    public function test_sending_duplicated_invoice_number_returns_duplicated_error(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
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
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $json = json_decode($this->getFilesContents('tbai-sample-with-multiple-same-vat.json'), true);
        $json['invoice']['header']['invoiceNumber'] = (string)time();
        sleep(1);

        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $this->assertFalse($response->isCorrect());
        $this->assertFalse($response->isDelivered());
    }

    public function test_TicketBai_response_headers_can_be_retrieved(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $this->assertIsArray($response->headers());
        $this->assertArrayHasKey('eus-bizkaia-n3-tipo-respuesta', $response->headers());
    }

    public function test_headers_can_be_retrieved_on_incorrect_response(): void
    {
        [$privateKey, $password] = $this->getBizkaiaP12Credentials();

        $json = json_decode($this->getFilesContents('tbai-sample-with-multiple-same-vat.json'), true);
        $json['invoice']['header']['invoiceNumber'] = (string)time();
        sleep(1);

        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $signedFilename = $this->signFile($ticketbai, $privateKey, $password);

        $endpoint = new Api(TicketBai::TERRITORY_BIZKAIA, true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $this->assertFalse($response->isCorrect());
        $this->assertFalse($response->isDelivered());

        $this->assertIsArray($response->headers());
        $this->assertArrayHasKey('eus-bizkaia-n3-tipo-respuesta', $response->headers());
    }

    private function getBizkaiaP12Credentials(): array
    {
        return [
            PrivateKey::p12($_ENV['TBAI_BIZKAIA_P12_PATH']), 
            $_ENV['TBAI_BIZKAIA_PRIVATE_KEY']
        ];
    }

    private function getBizkaiaPemCredentials(): array
    {
        return [
            PrivateKey::pem($_ENV['TBAI_TEST_SINGLE_PEM_PATH'], $_ENV['TBAI_TEST_SINGLE_PEM_PATH']),
            $_ENV['TBAI_TEST_SINGLE_PEM_PASSWORD']
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

    private function debugResponseWithFile($endpoint, TicketBai | TicketBaiCancel $ticketbai, $response, string $signedFile, string $responseFile): void
    {
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
            echo "Signed file: " . basename($signedFile) . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
    }

    private function assertSubmissionSuccessful(Api|Endpoint $endpoint, TicketBai|TicketBaiCancel $ticketbai, PrivateKey $key, string $password, string $signedFile): void
    {
        $response = $endpoint->submitInvoice($ticketbai, $key, $password, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $this->saveResponseToFile($response);
        $this->debugResponseWithFile($endpoint, $ticketbai, $response, $signedFile, $responseFile);
        $this->assertTrue($response->isCorrect());
    }

}
