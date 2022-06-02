<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\Api\Araba\Endpoint as ArabaEndpoint;
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint as GipuzkoaEndpoint;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\Qr;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
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

        // $qr = new Qr($ticketbai);
        // var_dump($qr->ticketbaiIdentifier());
        // var_dump($qr->qrUrl());
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

        // $qr = new Qr($ticketbai);
        // var_dump($qr->ticketbaiIdentifier());
        // var_dump($qr->qrUrl());
    }

    public function test_TicketBai_QR_can_be_generated_for_araba(): void
    {
        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);

        $qr = new Qr($ticketbai, true);
        $this->assertEquals(39, strlen($qr->ticketbaiIdentifier()));
        $this->assertStringContainsString('https://pruebas-ticketbai.araba.eus/tbai/qrtbai/?id=' . $qr->ticketbaiIdentifier(), $qr->qrUrl());
    }

    public function test_TicketBai_QR_can_be_generated_for_bizkaia(): void
    {
        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_BIZKAIA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'], $filename);

        $qr = new Qr($ticketbai, true);
        $this->assertEquals(39, strlen($qr->ticketbaiIdentifier()));
        $this->assertStringContainsString('https://batuz.eus/QRTBAI/?id=' . $qr->ticketbaiIdentifier(), $qr->qrUrl());
    }

    public function test_TicketBai_QR_can_be_generated_for_gipuzkoa(): void
    {
        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_GIPUZKOA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'], $filename);

        $qr = new Qr($ticketbai, true);
        $this->assertEquals(39, strlen($qr->ticketbaiIdentifier()));
        $this->assertStringContainsString('https://tbai.prep.gipuzkoa.eus/qr/?id=' . $qr->ticketbaiIdentifier(), $qr->qrUrl());
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
        // var_dump($filename);exit();

        try {
            XAdES::verifyDocument(
                $filename
            );
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

        // $qr = new Qr($ticketbai);
        // var_dump($qr->ticketbaiIdentifier());
        // var_dump($qr->qrUrl());
    }
}
