<?php

namespace Test\Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Test\Barnetik\TestCase;

class EndpointTest extends TestCase
{
    const SUBMIT_RETRIES = 3;
    const SUBMIT_RETRY_DELAY = 3;
    const DEFAULT_TERRITORY = TicketBai::TERRITORY_GIPUZKOA;

    public function test_TicketBai_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_delivered_for_intracomunitary_with_vat(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBaiFromJson(
            __DIR__ . '/../../__files/tbai-intracomunitary-eu-vat-id-recipient.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_GIPUZKOA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_exports(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBaiFromJson(
            __DIR__ . '/../../__files/tbai-export.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_GIPUZKOA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_is_delivered_for_ISP(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBaiFromJson(
            __DIR__ . '/../../__files/tbai-sample-isp.json'
        );
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Api(TicketBai::TERRITORY_GIPUZKOA, true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isCorrect());
    }

    public function test_TicketBai_multiVat_is_delivered(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_GIPUZKOA);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertFalse($response->hasErrorData());
        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_canceled(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);
        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiCancel = $this->ticketBaiMother->createTicketBaiCancelForInvoice($ticketbai);
        $signedFilename = $signedFilename . '-cancel.xml';
        $ticketbaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($ticketbaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            // echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_TicketBai_is_rectified(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $ticketbaiRectification = $this->ticketBaiMother->createGipuzkoaTicketBaiRectification($ticketbai);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($ticketbaiRectification, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }
        $this->assertTrue($response->isDelivered());
    }

    public function test_json_data_is_correct_on_sample(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $json = $this->getFilesContents('tbai-sample.json');
        $jsonArray = json_decode($json, true);
        $jsonArray['invoice']['header']['invoiceNumber'] = time();
        sleep(1);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $jsonArray);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            // echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_json_data_is_correct_on_sample_with_multiple_same_vat(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $json = $this->getFilesContents('tbai-sample-with-multiple-same-vat.json');
        $jsonArray = json_decode($json, true);
        $jsonArray['invoice']['header']['invoiceNumber'] = time();
        sleep(1);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $jsonArray);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            // echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_json_data_is_correct_on_regimen_51_sample(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $json = $this->getFilesContents('tbai-sample-regimen-51.json');
        $jsonArray = json_decode($json, true);
        $jsonArray['invoice']['header']['invoiceNumber'] = time();
        sleep(1);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $jsonArray);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            // echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_json_data_is_correct_on_regimen_51_with_equivalence_sample(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $json = $this->getFilesContents('tbai-sample-regimen-51-with-equivalence.json');
        $jsonArray = json_decode($json, true);
        $jsonArray['invoice']['header']['invoiceNumber'] = time();
        sleep(1);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $jsonArray);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_foreign_invoice_can_be_created_with_foreign_breakdown_items(): void
    {
        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];

        sleep(1);

        $json = array(
            'territory' => '03',
            'subject' => array(
                'issuer' => array(
                    'vatId' => $nif,
                    'name' => $issuer,
                ),
                'recipients' => array(
                    0 => array(
                        'vatId' => 'MSU871403S',
                        'vatIdType' => '03',
                        'name' => 'Alayn Chequebara',
                        'postalCode' => '00522',
                        'address' => 'Puerto Principe, 33',
                        'countryCode' => 'HR',
                    ),
                ),
                'issuedBy' => 'N',
            ),
            'invoice' => array(
                'header' => array(
                    'series' => 'A',
                    'invoiceNumber' => time(),
                    'expeditionDate' => date('d-m-Y'),
                    'expeditionTime' => date('H:i:s'),
                    'simplifiedInvoice' => false,
                ),
                'data' => array(
                    'description' => 'FAC2022A1',
                    'details' => array(
                        0 => array(
                            'description' => 'Producto 1',
                            'unitPrice' => 10.0,
                            'quantity' => 1.0,
                            'discount' => 0.0,
                            'totalAmount' => 12.1,
                        ),
                    ),
                    'total' => 12.1,
                    'vatRegimes' => array(
                        0 => '01',
                    ),
                    'supportedRetention' => NULL,
                ),
                'breakdown' => array(
                    'foreignDeliverySubjectNotExemptBreakdownItems' => array(
                        0 => array(
                            'type' => 'S1',
                            'vatDetails' => array(
                                0 => array(
                                    'taxBase' => 10.0,
                                    'taxRate' => 21.0,
                                    'taxQuota' => 2.1,
                                    'equivalenceRate' => NULL,
                                    'equivalenceQuota' => NULL,
                                    'isEquivalenceOperation' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $json);
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
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
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $jsonString = json_encode($ticketbai->toArray());
        $json = json_decode($jsonString, true);

        $restoredTbai = TicketBai::createFromJson($this->ticketBaiMother->createGipuzkoaVendor(), $json);
        $restoredTbai->setSignedXmlPath($signedFilename);

        $endpoint = new Endpoint(true, true);
        $response = $endpoint->submitInvoice($restoredTbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
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
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaWrongTicketBai();

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzendu = $this->ticketBaiMother->createZuzenduToModifyWrongTicketBai($ticketbai);

        $response = $endpoint->submitZuzendu($zuzendu, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
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
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $signedFilename);
        $endpoint = new Endpoint(true, true);
        $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        sleep(1);

        $wrongTicketBaiCancel = $this->ticketBaiMother->createGipuzkoaTicketBaiCancel();
        $signedFilename = $signedFilename . 'wrong-cancel.xml';
        $wrongTicketBaiCancel->sign($privateKey, $certPassword, $signedFilename);
        $response = $endpoint->cancelInvoice($wrongTicketBaiCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $zuzenduCancel = $this->ticketBaiMother->createZuzenduCancelForTicketBai($wrongTicketBaiCancel, $ticketbai);
        $response = $endpoint->cancelZuzendu($zuzenduCancel, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Date:" . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Sent file: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Signed file: " . basename($signedFilename) . "\n";
            echo "Main error message: " . $response->mainErrorMessage() . "\n";
            echo "Response file: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }

    public function test_sending_duplicated_invoice_number_returns_duplicated_error(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);
        $responseFile = $responseFile . '-duplicated';
        file_put_contents($responseFile, $response->content());

        $registryError = $response->errorDataRegistry();
        $this->assertTrue($response->hasErrorData());
        $this->assertArrayHasKey('errorCode', $registryError[0]);
        $this->assertArrayHasKey('errorMessage', $registryError[0]);
        $this->assertEquals('005', $registryError[0]['errorCode']);
        $this->assertEquals('El fichero ya se ha recibido anteriormente.', $registryError[0]['errorMessage']['es']);
        $this->assertEquals('Fitxategia lehenago jaso da.', $registryError[0]['errorMessage']['eu']);

        $this->assertFalse($response->isDelivered());
        $this->assertFalse($response->isCorrect());
    }

    public function test_TicketBai_response_headers_can_be_retrieved(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $privateKey, $certPassword, self::SUBMIT_RETRIES, self::SUBMIT_RETRY_DELAY);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', date('YmdHis') . '-response-');
        $response->saveResponseContent($responseFile);

        if (!$response->isCorrect()) {
            echo "\n";
            echo "VatId / IFZ / NIF: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
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
