<?php

namespace Test\Barnetik\Tbai;

use Barnetik\Tbai\Api;
use Barnetik\Tbai\PrivateKey;
use Barnetik\Tbai\Qr;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use Test\Barnetik\TestCase;

class QrTest extends TestCase
{
    public function test_TicketBai_QR_can_be_generated_for_araba(): void
    {
        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $ticketbai->sign($privateKey, $_ENV['TBAI_ARABA_PRIVATE_KEY'], $filename);

        $qr = new Qr($ticketbai, true);
        $requestData = parse_url($qr->qrUrl());
        $this->assertEquals('pruebas-ticketbai.araba.eus', $requestData['host']);
        $this->assertEquals('/tbai/qrtbai/', $requestData['path']);

        $this->assertQrUrlQuery($qr);
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
        $requestData = parse_url($qr->qrUrl());
        $this->assertEquals('batuz.eus', $requestData['host']);
        $this->assertEquals('/QRTBAI/', $requestData['path']);

        $this->assertQrUrlQuery($qr);
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
        $requestData = parse_url($qr->qrUrl());
        $this->assertEquals('tbai.prep.gipuzkoa.eus', $requestData['host']);
        $this->assertEquals('/qr/', $requestData['path']);

        $this->assertQrUrlQuery($qr);
    }

    private function assertQrUrlQuery(Qr $qr): void
    {
        $query = $this->getQueryParams($qr->qrUrl());
        $this->assertEquals(39, mb_strlen($qr->ticketbaiIdentifier()));
        $this->assertEquals($qr->ticketbaiIdentifier(), $query['id']);
        $this->assertArrayHasKey('id', $query);
        $this->assertArrayHasKey('s', $query);
        $this->assertArrayHasKey('nf', $query);
        $this->assertArrayHasKey('i', $query);
        $this->assertArrayHasKey('cr', $query);
    }


    public function test_TicketBai_QR_params_are_urlencoded(): void
    {
        $rareNif = '4@+79?78Ç';
        $signature = 'o&pAe%vCorn+p';
        $expeditionDate = new Date(date('d-m-Y'));
        $series = 'T@S?SER%&E+';
        $invoiceNumber = '00&0@11';
        $amount = new Amount("1234.23");

        $vatId = $this->createMock(VatId::class);
        $vatId->method('__toString')->willReturn($rareNif);

        $tbaiMock = $this->createMock(TicketBai::class);
        $tbaiMock->method('issuerVatId')->willReturn($vatId);
        $tbaiMock->method('expeditionDate')->willReturn($expeditionDate);
        $tbaiMock->method('shortSignatureValue')->willReturn($signature);
        $tbaiMock->method('territory')->willReturn(TicketBai::TERRITORY_ARABA);
        $tbaiMock->method('series')->willReturn($series);
        $tbaiMock->method('invoiceNumber')->willReturn($invoiceNumber);
        $tbaiMock->method('totalAmount')->willReturn($amount);

        $qr = new Qr($tbaiMock, true);

        $query = $this->getQueryParams($qr->qrUrl());
        $this->assertEquals($series, $query['s']);
        $this->assertEquals($invoiceNumber, $query['nf']);
        $this->assertEquals((string)$amount, $query['i']);
    }

    public function test_TicketBai_Araba_QR_saves_png_to_path(): void
    {
        $ticketbai = $this->ticketBaiMother->createArabaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_ARABA_P12_PATH']);
        $certPassword = $_ENV['TBAI_ARABA_PRIVATE_KEY'];
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $api = Api::createForTicketBai($ticketbai, true, true);
        $api->submitInvoice($ticketbai, $privateKey, $certPassword);

        $qr = new Qr($ticketbai, true);
        $filename = tempnam(__DIR__ . '/__files/qr', 'qr-araba-');
        rename($filename, $filename . '.png');
        $filename .= '.png';
        $qr->savePng($filename);
        $this->assertFileExists($filename);
    }

    public function test_TicketBai_Bizkaia_QR_saves_png_to_path(): void
    {
        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_BIZKAIA_P12_PATH']);
        $certPassword = $_ENV['TBAI_BIZKAIA_PRIVATE_KEY'];
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $api = Api::createForTicketBai($ticketbai, true, true);
        $api->submitInvoice($ticketbai, $privateKey, $certPassword);

        $qr = new Qr($ticketbai, true);
        $filename = tempnam(__DIR__ . '/__files/qr', 'qr-bizkaia-');
        rename($filename, $filename . '.png');
        $filename .= '.png';
        $qr->savePng($filename);
        $this->assertFileExists($filename);
    }

    public function test_TicketBai_Gipuzkoa_QR_saves_png_to_path(): void
    {
        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $filename = tempnam(__DIR__ . '/__files/signedXmls', 'signed-');
        rename($filename, $filename . '.xml');
        $filename .= '.xml';
        $privateKey = PrivateKey::p12($_ENV['TBAI_GIPUZKOA_P12_PATH']);
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $ticketbai->sign($privateKey, $certPassword, $filename);

        $api = Api::createForTicketBai($ticketbai, true, true);
        $api->submitInvoice($ticketbai, $privateKey, $certPassword);

        $qr = new Qr($ticketbai, true);
        $filename = tempnam(__DIR__ . '/__files/qr', 'qr-gipuzkoa-');
        rename($filename, $filename . '.png');
        $filename .= '.png';
        $qr->savePng($filename);
        $this->assertFileExists($filename);
    }

    private function getQueryParams(string $url): array
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        $query = [];
        parse_str($queryString, $query);
        return $query;
    }
}
