<?php
namespace Test\Barnetik\Tbai\Mother;

use Barnetik\Tbai\Fingerprint;
use Barnetik\Tbai\Invoice\Data\Detail;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\Invoice\Breakdown;
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

class TicketBaiMother
{
    public function createTicketBai(string $nif, string $issuer, string $license, string $developer, string $appName, string $appVersion, string $territory): TicketBai
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

        $invoice = new Invoice($header, $data, $breakdown);
        return new TicketBai(
            $subject,
            $invoice,
            $fingerprint,
            $territory
        );
    }

    private function getSubject(string $nif, string $name): Subject
    {
        $issuer = new Issuer(new VatId($nif), $name);
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Client Name', '48270', 'Markina-Xemein');
        return new Subject($issuer, $recipient, Subject::ISSUED_BY_ISSUER);
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