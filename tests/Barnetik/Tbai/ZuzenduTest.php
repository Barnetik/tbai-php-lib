<?php

namespace Barnetik\Tbai;

use Test\Barnetik\TestCase;

class ZuzenduTest extends TestCase
{
    public function test_Zuzendu_data_can_be_serialized(): void
    {
        $zuzendu = $this->getZuzendu();
        $this->assertIsString(json_encode($zuzendu->toArray()));
    }

    public function test_Zuzendu_validates_schema(): void
    {
        $zuzendu = $this->getZuzendu();
        $dom = $zuzendu->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/zuzendualta_ticketbaiv1-2-2.xsd'));
    }

    public function test_Zuzendu_can_be_generated_from_json(): void
    {
        $json = $this->getFilesContents('zuzendu-sample.json');
        $zuzendu = Zuzendu::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true));
        $this->assertEquals(
            Zuzendu::class,
            get_class($zuzendu)
        );

        $dom = $zuzendu->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/zuzendualta_ticketbaiv1-2-2.xsd'));
    }

    private function getZuzendu(): Zuzendu
    {
        $certFile = $_ENV['TBAI_ARABA_P12_PATH'];
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';

        $ticketbai = $this->ticketBaiMother->createArabaWrongTicketBai();
        $ticketbai->sign($privateKey, $certPassword, $filename);

        return $this->ticketBaiMother->createZuzenduToModifyWrongTicketBai($ticketbai);
    }
}
