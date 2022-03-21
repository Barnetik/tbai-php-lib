<?php

namespace Test\Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Gipuzkoa\Endpoint;
use Barnetik\Tbai\SubmitInvoiceFile;
use PHPUnit\Framework\TestCase;
use Test\Barnetik\Tbai\Mother\TicketBaiMother;

class EndpointTest extends TestCase
{
    const DEFAULT_TERRITORY = SubmitInvoiceFile::TERRITORY_GIPUZKOA;
    private TicketBaiMother $ticketBaiMother;

    protected function setUp(): void
    {
        $this->ticketBaiMother = new TicketBaiMother;
    }

    public function test_TicketBai_is_delivered(): void
    {
        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];

        $ticketbai = $this->ticketBaiMother->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, SubmitInvoiceFile::TERRITORY_GIPUZKOA);
        $signedFilename = tempnam(__DIR__ . '/../../__files/signedXmls', 'signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];

        $ticketbai->sign($certFile, $certPassword, $signedFilename);

        $endpoint = new Endpoint(true, true);

        $response = $endpoint->submitInvoice($ticketbai, $certFile, $certPassword);

        $responseFile = tempnam(__DIR__ . '/../../__files/responses', 'response-');
        file_put_contents($responseFile, $response->content());

        if (!$response->isCorrect()) {
            echo "\n";
            echo "IFZ: " . $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'] . "\n";
            echo "Data: " . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . file_get_contents('https://ipecho.net/plain') . "\n";
            echo "Bidalitako fitxategia: " . $endpoint->debugData(AbstractTerritory::DEBUG_SENT_FILE) . "\n";
            echo "Sinatutako fitxategia: " . basename($signedFilename) . "\n";
            echo "Jasotako errore printzipala: " . $response->mainErrorMessage() . "\n";
            echo "Erantzuna: " . basename($responseFile) . "\n";
        }

        $this->assertTrue($response->isDelivered());
    }
}
