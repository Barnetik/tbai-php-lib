<?php
namespace Test\Barnetik\Tbai\Mother;

use Barnetik\Tbai\CancelInvoice\Header as CancelInvoiceHeader;
use Barnetik\Tbai\CancelInvoice\InvoiceId;
use Barnetik\Tbai\Fingerprint;
use Barnetik\Tbai\Invoice\Data\Detail;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\ForeignServiceSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\VatDetail;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\Subject;
use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\TicketBaiCancel;

class TicketBaiMother
{
    public function createTicketBai(string $nif, string $issuer, string $license, string $developer, string $appName, string $appVersion, string $territory, bool $selfEmployed = false): TicketBai
    {
        $subject = $this->getSubject($nif, $issuer);
        $fingerprint = $this->getFingerprint($license, $developer, $appName, $appVersion);

        $header = Header::create((string)time(), new Date(date('d-m-Y')), new Time(date('H:i:s')), 'TESTSERIE');
        sleep(1); // Avoid same invoice number as time is used for generation
        $data = new Data('factura ejemplo TBAI', new Amount('89.36'), [Data::VAT_REGIME_01]);
        $data->addDetail(new Detail('Artículo 1 Ejemplo', new Amount('23.356', 12, 8), new Amount('1'), new Amount('25.84'), new Amount('2.00')));
        $data->addDetail(new Detail('Artículo 2 xxx', new Amount('18.2', 12, 8), new Amount('1.50'), new Amount('33.03')));
        $data->addDetail(new Detail('Artículo 3 aaaaaaa', new Amount('1.40', 12, 8), new Amount('18'), new Amount('30.49')));

        $breakdown = new Breakdown();
        // $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem(new Amount('14.93'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
        // $breakdown->addNationalSubjectExemptBreakdownItem(new NationalSubjectExemptBreakdownItem(new Amount('56.78'), NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_23));

        $vatDetail = new VatDetail(new Amount('73.86'), new Amount('21'), new Amount('15.50'));
        $notExemptBreakdown = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
        $breakdown->addNationalSubjectNotExemptBreakdownItem($notExemptBreakdown);

        // $foreignServiceSubjectNotExemptBreakdown = new ForeignServiceSubjectNotExemptBreakdownItem(ForeignServiceSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
        // $breakdown->addForeignServiceSubjectNotExemptBreakdownItem($foreignServiceSubjectNotExemptBreakdown);

        $invoice = new Invoice($header, $data, $breakdown);
        return new TicketBai(
            $subject,
            $invoice,
            $fingerprint,
            $territory,
            $selfEmployed
        );
    }

    public function createTicketBaiCancel(string $nif, string $issuerName, string $license, string $developer, string $appName, string $appVersion, string $territory, bool $selfEmployed = false): TicketBaiCancel
    {
        $issuer = new Issuer(new VatId($nif), $issuerName);
        $header = CancelInvoiceHeader::create((string)time(), new Date(date('d-m-Y')), 'TESTSERIE');
        $invoiceId = new InvoiceId($issuer, $header);
        $fingerprint = $this->getFingerprint($license, $developer, $appName, $appVersion);

        return new TicketBaiCancel($invoiceId, $fingerprint, $territory, $selfEmployed);
    }

    public function createTicketBaiCancelForInvoice(TicketBai $ticketbai): TicketBaiCancel
    {
        $header = CancelInvoiceHeader::create($ticketbai->invoiceNumber(), $ticketbai->expeditionDate(), $ticketbai->series());
        $invoiceId = new InvoiceId($ticketbai->issuer(), $header);
        return new TicketBaiCancel($invoiceId, $ticketbai->fingerprint(), $ticketbai->territory(), $ticketbai->selfEmployed());
    }

    public function createArabaVendor(): Vendor
    {
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];
        return new Vendor($license, $developer, $appName, $appVersion);
    }

    public function createBizkaiaVendor(): Vendor
    {
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];
        return new Vendor($license, $developer, $appName, $appVersion);
    }

    public function createGipuzkoaVendor(): Vendor
    {
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];
        return new Vendor($license, $developer, $appName, $appVersion);
    }

    public function createArabaTicketBai(): TicketBai
    {
        $nif = $_ENV['TBAI_ARABA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_ARABA_ISSUER_NAME'];
        $license = $_ENV['TBAI_ARABA_APP_LICENSE'];
        $developer = $_ENV['TBAI_ARABA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_ARABA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_ARABA_APP_VERSION'];

        return $this->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_ARABA);
    }


    public function createBizkaiaTicketBai(): TicketBai
    {
        $nif = $_ENV['TBAI_BIZKAIA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_BIZKAIA_ISSUER_NAME'];
        $license = $_ENV['TBAI_BIZKAIA_APP_LICENSE'];
        $developer = $_ENV['TBAI_BIZKAIA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_BIZKAIA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_BIZKAIA_APP_VERSION'];

        return $this->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_BIZKAIA);
    }


    public function createGipuzkoaTicketBai(): TicketBai
    {
        $nif = $_ENV['TBAI_GIPUZKOA_ISSUER_NIF'];
        $issuer = $_ENV['TBAI_GIPUZKOA_ISSUER_NAME'];
        $license = $_ENV['TBAI_GIPUZKOA_APP_LICENSE'];
        $developer = $_ENV['TBAI_GIPUZKOA_APP_DEVELOPER_NIF'];
        $appName = $_ENV['TBAI_GIPUZKOA_APP_NAME'];
        $appVersion =  $_ENV['TBAI_GIPUZKOA_APP_VERSION'];

        return $this->createTicketBai($nif, $issuer, $license, $developer, $appName, $appVersion, TicketBai::TERRITORY_GIPUZKOA);
    }

    private function getSubject(string $nif, string $name): Subject
    {
        $issuer = new Issuer(new VatId($nif), $name);
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Client Name', '48270', 'Markina-Xemein');
        return new Subject($issuer, $recipient, Subject::ISSUED_BY_THIRD_PARTY);
    }

    private function getForeignSubject(string $nif, string $name): Subject
    {
        $issuer = new Issuer(new VatId($nif, VatId::VAT_ID_TYPE_PASSPORT), $name);
        $recipient = Recipient::createGenericRecipient(new VatId('00000000T', VatId::VAT_ID_TYPE_PASSPORT), 'Client Name', '48270', 'Markina-Xemein', 'IE');
        return new Subject($issuer, $recipient, Subject::ISSUED_BY_THIRD_PARTY);
    }

    private function getFingerprint(string $license, string $developer, string $appName, string $appVersion): Fingerprint
    {
        $vendor = new Vendor($license, $developer, $appName, $appVersion);
        // $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
        // return new Fingerprint($vendor, $previousInvoice);
        // $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TESTSERIE');
        return new Fingerprint($vendor);
    }
}
