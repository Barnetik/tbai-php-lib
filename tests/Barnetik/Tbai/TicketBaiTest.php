<?php

namespace Test\Barnetik\Tbai;

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

    public function test_unsigned_TicketBai_validates_schema(): void
    {
        $ticketbai = $this->getTicketBai();
        $dom = $ticketbai->dom();
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }

    public function test_TicketBai_can_be_signed_with_PFX_key(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $ticketbai->sign($_ENV['TBAI_ARABA_P12_PATH'], $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));

        // $qr = new Qr($ticketbai);
        // var_dump($qr->ticketbaiIdentifier());
        // var_dump($qr->qrUrl());
    }

    public function test_TicketBai_signed_file_is_valid(): void
    {
        $ticketbai = $this->getTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $ticketbai->sign($_ENV['TBAI_ARABA_P12_PATH'], $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);
        $signedDom = new DOMDocument();
        $signedDom->load($filename);

        try {
            XAdES::verifyDocument(
                $filename
            );
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
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
}
