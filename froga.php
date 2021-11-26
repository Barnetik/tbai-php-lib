<?php

use Barnetik\Tbai\Fingerprint;
use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Invoice;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\VatDetail;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use Barnetik\Tbai\LROE;
use Barnetik\Tbai\Subject;
use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use Barnetik\Tbai\ValueObject\VatId;

include (__DIR__ . '/vendor/autoload.php');

$certFile = __DIR__ . '/tests/__files/EnpresaZigilua_SelloDeEmpresa.p12';
$certPassword = 'IZDesa2021';

$ticketBai = getTicketBai($certFile, $certPassword);

$lroe = new LROE(LROE::ENDPOINT_BIZKAIA);
$lroe->submitInvoice($ticketBai, $certFile, $certPassword);

function getTicketBai(string $certFile, string $certPassword): TicketBai
{
    $subject = getSubject();
    $fingerprint = getFingerprint();

    $header = Header::create('0000002', new Date(date('d-m-Y')), new Time(date('H:i:s')), 'TEST-SERIE-');
    $data = new Data('test-description', new Ammount('12.34'), [Data::VAT_REGIME_01]);
    $breakdown = new Breakdown();
    $breakdown->addNationalNotSubjectBreakdownItem(new NationalNotSubjectBreakdownItem(new Ammount('12.34'), NationalNotSubjectBreakdownItem::NOT_SUBJECT_REASON_LOCATION_RULES));
    $breakdown->addNationalSubjectExemptBreakdownItem(new NationalSubjectExemptBreakdownItem(new Ammount('56.78'), NationalSubjectExemptBreakdownItem::EXEMPT_REASON_ART_23));

    $vatDetail = new VatDetail(new Ammount('98.76'), new Ammount('4.12'), new Ammount('3.01'));
    $notExemptBreakdown = new NationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::NOT_EXEMPT_TYPE_S1, [$vatDetail]);
    $breakdown->addNationalSubjectNotExemptBreakdownItem($notExemptBreakdown);

    $invoice = new Invoice($header, $data, $breakdown);

    $ticketbai = new TicketBai(
        $subject,
        $invoice,
        $fingerprint
    );

    $resultFile = __DIR__ . '/froga-signed.xml';
    $ticketbai->sign($certFile, $certPassword, dirname($resultFile), basename($resultFile));
    return $ticketbai;
}

function getSubject(): Subject
{
    $emitter = new Issuer(new VatId('A99800005'), 'TBAIBI00000000PRUEBA');
    $recipient = Recipient::createNationalRecipient(new VatId('F95780987'), 'Barnetik Koop. Client');
    return new Subject($emitter, $recipient, Subject::EMITTED_BY_EMITTER);
}

function getMultipleRecipientSubject(): Subject
{
    $subject = getSubject();
    $subject->addRecipient(Recipient::createGenericRecipient(new VatId('X0000000I', VatId::VAT_ID_TYPE_RESIDENCE_CERTIFICATE), 'Client Name 2', '48270', 'IE'));
    return $subject;
}

function getFingerprint(): Fingerprint
{
    $vendor = new Vendor('TBAIBI00000000PRUEBA', 'A99800005', 'SOFTWARE GARANTE TICKETBAI PRUEBA', '1.0');
    $previousInvoice = new PreviousInvoice('0000002', new Date('02-12-2020'), 'abcdefgkauskjsa', 'TEST-SERIE-');
    return new Fingerprint($vendor, $previousInvoice);
}

