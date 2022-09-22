<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\Header\RectifiedInvoice;
use Barnetik\Tbai\Header\RectifyingInvoice;
use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\VatDetail;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Data\Detail;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class SimplifiedRectificationTicketBaiTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_TicketBai_rectification_validates_schema(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $ticketbaiRectification = $this->ticketBaiMother->createGipuzkoaTicketBaiSimplifiedRectification($ticketbai);
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);
        $this->assertStringContainsString('FacturaRectificativa', (string)$ticketbaiRectification);

        $signedDom = new DOMDocument();
        $signedDom->load($signedFilename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2.xsd'));
    }

    public function test_ticketbai_simplified_can_be_generated_from_json(): void
    {
        $json = file_get_contents(__DIR__ . '/__files/tbai-simplified-rectification-without-recipient-sample.json');
        $ticketbai = TicketBai::createFromJson($this->ticketBaiMother->createArabaVendor(), json_decode($json, true));
        $this->assertEquals(
            TicketBai::class,
            get_class($ticketbai)
        );

        $dom = $ticketbai->dom();
        $this->assertStringContainsString('<FacturaRectificativa><Codigo>R5</Codigo><Tipo>S</Tipo>', (string)$ticketbai);
        $this->assertTrue($dom->schemaValidate(__DIR__ . '/__files/specs/ticketBaiV1-2-no-signature.xsd'));
    }


}
