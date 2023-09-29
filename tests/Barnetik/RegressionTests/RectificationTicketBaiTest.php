<?php

namespace Test\Barnetik\RegressionTests;

use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class RectificationTicketBaiTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    /**
     * https://github.com/Barnetik/tbai-php-lib/issues/35
     */
    public function test_gh35_TicketBai_create_rectification_from_xml_loads_correct_invoice_number(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();

        $ticketbaiRectification = $this->ticketBaiMother->createGipuzkoaTicketBaiRectification($ticketbai);
        $signedRectificationFile = $this->getSignedDestinationFile();
        $ticketbaiRectification->sign($privateKey, $certPassword, $signedRectificationFile);
        $ticketbaiFromXml = TicketBai::createFromXml(file_get_contents($signedRectificationFile), $ticketbai->territory());
        
        $rectificationArray = $ticketbaiRectification->toArray();
        $ticketbaiFromXmlArray = $ticketbaiFromXml->toArray();
        $this->assertEquals($rectificationArray['invoice']['header']['rectifiedInvoices'][0]['invoiceNumber'], $ticketbaiFromXmlArray['invoice']['header']['rectifiedInvoices'][0]['invoiceNumber']);

    }

    private function getSignedDestinationFile(): string
    {
        $filename = tempnam(__DIR__ . '/../Tbai/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        return $filename . '.xml';
    }

}
