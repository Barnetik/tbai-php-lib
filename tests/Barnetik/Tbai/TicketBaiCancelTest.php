<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class TicketBaiCancelTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_ticketbai_can_be_generated_from_json(): void
    {
        $json = file_get_contents(__DIR__ . '/__files/tbai-sample.json');
        $this->assertEquals(
            TicketBai::class,
            get_class(TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true)))
        );
    }

    public function test_ticketbai_can_be_generated_from_xml(): void
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-cancel-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->ticketBaiMother->createArabaTicketBaiCancel();
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        $ticketbaiFromXml = TicketBaiCancel::createFromXml($signedDom->saveXML(), $ticketbai->territory());

        $signedDom = new DOMDocument();
        $signedDom->loadXML($ticketbaiFromXml->signed());

        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/Anula_ticketBaiV1-2.xsd'));
    }

    public function test_TicketBaiCancel_data_can_be_serialized(): void
    {
        $ticketbai = $this->ticketBaiMother->createArabaTicketBaiCancel();
        // echo json_encode($ticketbai->toArray());
        $this->assertIsString(json_encode($ticketbai->toArray()));
    }

    public function test_signed_TicketBaiCancel_validates_schema(): void
    {
        $ticketbai = $this->ticketBaiMother->createArabaTicketBaiCancel();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-cancel-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/Anula_ticketBaiV1-2.xsd'));
    }
}
