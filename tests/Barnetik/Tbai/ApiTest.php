<?php
namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\Api\Araba\Endpoint as ArabaEndpoint;
use Barnetik\Tbai\Api\Bizkaia\Endpoint as BizkaiaEndpoint;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint as GipuzkoaEndpoint;
use Barnetik\Tbai\SubmitInvoiceFile;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class ApiTest extends TestCase
{
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_create_by_ticketbai_uses_correct_endpoint(): void
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_ARABA);
        $api = Api::createForTicketBai($ticketbai);
        $this->assertEquals(ArabaEndpoint::class, get_class($api->endpoint()));

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_BIZKAIA);
        $api = Api::createForTicketBai($ticketbai);
        $this->assertEquals(BizkaiaEndpoint::class, get_class($api->endpoint()));

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_GIPUZKOA);
        $api = Api::createForTicketBai($ticketbai);
        $this->assertEquals(GipuzkoaEndpoint::class, get_class($api->endpoint()));
    }
}