<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\ValueObject\VatId;
use PHPUnit\Framework\TestCase;

class InvoiceSubjectTest extends TestCase
{
    public function test_single_recipient_subject(): void
    {
        $emitter = new Issuer(new VatId('11111111H'), 'Emitter Bussiness');
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Recipient Bussiness', '48270', 'Markina-Xemein');
        $invoiceSubject = new Subject($emitter, $recipient, Subject::ISSUED_BY_ISSUER);

        $this->assertFalse($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('N', $invoiceSubject->multipleRecipients());
        $this->assertCount(1, $invoiceSubject->recipients());
    }

    public function test_multiple_recipient_subjects(): void
    {
        $emitter = new Issuer(new VatId('11111111H'), 'Emitter Bussiness');
        $recipient1 = Recipient::createNationalRecipient(new VatId('00000000T'), 'Recipient Bussiness 1', '48270', 'Markina-Xemein');
        $recipient2 = Recipient::createNationalRecipient(new VatId('00000001R'), 'Recipient Bussiness 2', '48270', 'Markina-Xemein');
        $recipient3 = Recipient::createNationalRecipient(new VatId('00000002W'), 'Recipient Bussiness 3', '48270', 'Markina-Xemein');

        $invoiceSubject = new Subject($emitter, $recipient1, Subject::ISSUED_BY_ISSUER);
        $invoiceSubject->addRecipient($recipient2);
        $invoiceSubject->addRecipient($recipient3);

        $this->assertTrue($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('S', $invoiceSubject->multipleRecipients());
        $this->assertCount(3, $invoiceSubject->recipients());
    }
}
