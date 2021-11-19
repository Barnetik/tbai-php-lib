<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\ValueObject\VatId;
use PHPUnit\Framework\TestCase;

class InvoiceSubjectTest extends TestCase
{
    public function test_single_recipient_subject(): void
    {
        $emitter = new Emitter(new VatId('11111111H'), 'Emitter Bussiness');
        $recipient = Recipient::createNationalRecipient(new VatId('00000000T'), 'Recipient Bussiness');
        $invoiceSubject = new Subject($emitter, $recipient, Subject::EMITTED_BY_EMITTER);

        $this->assertFalse($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('N', $invoiceSubject->multipleRecipients());
        $this->assertCount(1, $invoiceSubject->recipients());
    }

    public function test_multiple_recipient_subjects(): void
    {
        $emitter = new Emitter(new VatId('11111111H'), 'Emitter Bussiness');
        $recipient1 = Recipient::createNationalRecipient(new VatId('00000000T'), 'Recipient Bussiness 1');
        $recipient2 = Recipient::createNationalRecipient(new VatId('00000001R'), 'Recipient Bussiness 2');
        $recipient3 = Recipient::createNationalRecipient(new VatId('00000002W'), 'Recipient Bussiness 3');

        $invoiceSubject = new Subject($emitter, $recipient1, Subject::EMITTED_BY_EMITTER);
        $invoiceSubject->addRecipient($recipient2);
        $invoiceSubject->addRecipient($recipient3);

        $this->assertTrue($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('S', $invoiceSubject->multipleRecipients());
        $this->assertCount(3, $invoiceSubject->recipients());
    }
}
