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
use Test\Barnetik\TestCase;

class TicketBaiRectificationTest extends TestCase
{
    public function test_TicketBai_rectification_validates_schema(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $ticketbaiRectification = $this->ticketBaiMother->createGipuzkoaTicketBaiRectification($ticketbai);
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);

        $signedDom = new DOMDocument();
        $signedDom->load($signedFilename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }

    public function test_TicketBai_rectification_from_json_validates_schema(): void
    {
        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $ticketbai = $this->ticketBaiMother->createGipuzkoaTicketBai();
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbai->sign($privateKey, $certPassword, $signedFilename);

        $ticketbaiRectification = $this->ticketBaiMother->createGipuzkoaTicketBaiFromJson(__DIR__ . '/__files/tbai-rectification-sample.json');
        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);
        // echo json_encode($ticketbaiRectification->toArray(), JSON_PRETTY_PRINT);
        // exit();
        $signedDom = new DOMDocument();
        $signedDom->load($signedFilename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }


    public function test_TicketBaiRectification_of_invoice_without_lines_validates_schema(): void
    {
        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];
        $territory = TicketBai::TERRITORY_GIPUZKOA;

        $certFile = $_ENV['TBAI_GIPUZKOA_P12_PATH'];
        $certPassword = $_ENV['TBAI_GIPUZKOA_PRIVATE_KEY'];
        $privateKey = PrivateKey::p12($certFile);

        $previousInvoice = $this->ticketBaiMother->createEmptyTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, $territory);

        $subject = $this->ticketBaiMother->getSubject($nif, $issuer);
        $fingerprint = $this->ticketBaiMother->getFingerprint($license, $developer, $appName, $appVersion);

        $rectifyingInvoice = new RectifyingInvoice(
            RectifyingInvoice::CODE_R1,
            RectifyingInvoice::TYPE_SUSTITUTION,
        );

        $header = Header::createRectifyingInvoice((string)time(), new Date(date('d-m-Y')), new Time(date('H:i:s')), $rectifyingInvoice, 'R' . $this->ticketBaiMother->testSerie());
        $header->addRectifiedInvoice(new RectifiedInvoice(
            $previousInvoice->invoiceNumber(),
            $previousInvoice->expeditionDate(),
            $previousInvoice->series()
        ));

        sleep(1); // Avoid same invoice number as time is used for generation
        $data = new Data('factura ejemplo TBAI', new Amount('55.24'), [Data::VAT_REGIME_01]);
        $data->addDetail(new Detail('Artículo 1 Ejemplo', new Amount('23.356', 12, 8), new Amount('1'), new Amount('22.21'), new Amount('5')));
        $data->addDetail(new Detail('Artículo 2 xxx', new Amount('18.2', 12, 8), new Amount('1.50'), new Amount('33.03')));

        $breakdown = new Breakdown();

        $vatDetail = new VatDetail(new Amount('45.66'), new Amount('21'), new Amount('9.59'));
        $notExemptBreakdown = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
        $breakdown->addNationalSubjectNotExemptBreakdownItem($notExemptBreakdown);

        $invoice = new Invoice($header, $data, $breakdown);
        $ticketbaiRectification = new TicketBai(
            $subject,
            $invoice,
            $fingerprint,
            $territory
        );

        $signedFilename = tempnam(__DIR__ . '/__files/signedXmls',  date('YmdHis') . '-signed-');
        rename($signedFilename, $signedFilename . '.xml');
        $signedFilename = $signedFilename . '.xml';

        $ticketbaiRectification->sign($privateKey, $certPassword, $signedFilename);

        $signedDom = new DOMDocument();
        $signedDom->load($signedFilename);
        $this->assertTrue($signedDom->schemaValidate(__DIR__ . '/__files/specs/ticketbaiv1-2-2.xsd'));
    }
}
