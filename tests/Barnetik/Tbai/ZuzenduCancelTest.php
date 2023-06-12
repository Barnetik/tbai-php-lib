<?php

namespace Barnetik\Tbai;

use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class ZuzenduCancelTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_ZuzenduCancel_data_can_be_serialized(): void
    {
        $zuzenduCancel = $this->getZuzenduCancel();
        $this->assertIsString(json_encode($zuzenduCancel->toArray()));
    }

    public function test_ZuzenduCancel_validates_schema(): void
    {
        $zuzenduCancel = $this->getZuzenduCancel();
        $dom = $zuzenduCancel->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ZuzenduAnula_ticketBaiV1-0.xsd'));
    }

    public function test_ZuzenduCancel_can_be_generated_from_json(): void
    {
        $json = file_get_contents(__DIR__ . '/__files/zuzendu-cancel-sample.json');
        $zuzenduCancel = ZuzenduCancel::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true));
        $this->assertEquals(
            ZuzenduCancel::class,
            get_class($zuzenduCancel)
        );

        $dom = $zuzenduCancel->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ZuzenduAnula_ticketBaiV1-0.xsd'));
    }

    private function getZuzenduCancel(): ZuzenduCancel
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-cancel-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $ticketbaiCancel = $this->ticketBaiMother->createArabaTicketBaiCancel();
        $ticketbaiCancel->sign($privateKey, $certPassword, $filename);

        return $this->ticketBaiMother->createZuzenduCancelForTicketBai($ticketbaiCancel, $ticketbai);
    }
}
