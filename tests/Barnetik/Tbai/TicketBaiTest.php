<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use DOMXPath;
use Exception;
use lyquidity\xmldsig\XAdES;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class TicketBaiTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_ticketbai_data_can_be_serialized(): void
    {
        $ticketbai = $this->getTicketBai();
        $this->assertIsString(json_encode($ticketbai->toArray()));
    }

    public function test_gh19_serialization_returns_correct_selfEmployed(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample.json'), true);
        $json['self_employed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample.json'), true);
        $json['self_employed'] = false;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(false, $ticketbaiArray['selfEmployed']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample.json'), true);
        $json['selfEmployed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample.json'), true);
        $json['selfEmployed'] = false; //This has priority over self_employed
        $json['self_employed'] = true;
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(false, $ticketbaiArray['selfEmployed']);
    }

    public function test_gh19_serialization_batuzIncomeTaxes_is_correctly_handled(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample-self-employed.json'), true);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('selfEmployed', $ticketbaiArray);
        $this->assertEquals(true, $ticketbaiArray['selfEmployed']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample-self-employed.json'), true);
        unset($json['batuzIncomeTaxes']);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEmpty($ticketbaiArray['batuzIncomeTaxes']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample.json'), true);
        $json['batuzIncomeTaxes'] = [];
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEmpty($ticketbaiArray['batuzIncomeTaxes']);

        $json = json_decode(file_get_contents(__DIR__ . '/__files/tbai-sample-self-employed.json'), true);
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createBizkaiaVendor(), $json);
        $ticketbaiArray = $ticketbai->toArray();
        $this->assertArrayHasKey('batuzIncomeTaxes', $ticketbaiArray);
        $this->assertEquals("197330", $ticketbaiArray['batuzIncomeTaxes']['incomeTaxDetails'][0]['epigraph']);
    }

    public function test_unsigned_TicketBai_validates_schema(): void
    {
        $ticketbai = $this->getTicketBai();
        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }

    public function test_ticketbai_can_be_generated_from_json(): void
    {
        $json = file_get_contents(__DIR__ . '/__files/tbai-sample.json');
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true));
        $this->assertEquals(
            TicketBai::class,
            get_class($ticketbai)
        );

        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }

    public function test_ticketbai_can_be_generated_from_xml(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->getTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        $ticketbaiFromXml = TicketBai::createFromXml($signedDom->saveXML(), $ticketbai->territory());

        $signedDom = new DOMDocument();
        $signedDom->loadXML($ticketbaiFromXml->signed());

        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_PFX_key(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));
    }

    public function test_TicketBai_can_have_multiple_vats(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));
    }

    public function test_TicketBai_signed_file_is_valid(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        try {
            ob_start();
            XAdES::verifyDocument(
                $filename
            );
            ob_end_clean();
            $this->assertTrue(true);
        } catch (Exception $e) {
            var_dump($e->getFile());
            var_dump($e->getLine());
            $this->fail($e->getMessage());
        }
    }

    public function test_TicketBai_without_lines_validates_schema(): void
    {

        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createEmptyTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);

        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }

    private function getTicketBai(): TicketBai
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        return $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
    }

    public function test_TicketBai_issuer_name_allows_ampersands(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = 'Test with & on issuer name';
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiMultiVat($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));
    }

    public function test_gh29_TicketBai_sends_operation_date_element(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = 'Test with & on issuer name';
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBaiWithOperationDate($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $xpath = new DOMXPath($signedDom);
        $operationDateValue = $xpath->evaluate('string(/T:TicketBai/Factura/DatosFactura/FechaOperacion)');
        $this->assertFalse(empty($operationDateValue));
    }
}
