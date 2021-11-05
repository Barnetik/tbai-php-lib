<?php
namespace Barnetik\Tbai;

use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;
use PHPUnit\Framework\TestCase;

class InvoiceSubjectTest extends TestCase
{
    public function testSingleRecipientSubject(): void
    {
        $emitter = new Emitter('11111111H', 'Emitter Bussiness');
        $recipient = Recipient::createNationalRecipient('00000000T', 'Recipient Bussiness');
        $invoiceSubject = new InvoiceSubject($emitter, $recipient, InvoiceSubject::EMITTED_BY_EMITTER);

        $this->assertFalse($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('N', $invoiceSubject->multipleRecipients());
        $this->assertCount(1, $invoiceSubject->recipients());
    }

    public function testMultipleRecipientsSubject(): void
    {
        $emitter = new Emitter('11111111H', 'Emitter Bussiness');
        $recipient1 = Recipient::createNationalRecipient('00000000T', 'Recipient Bussiness 1');
        $recipient2 = Recipient::createNationalRecipient('00000001R', 'Recipient Bussiness 2');
        $recipient3 = Recipient::createNationalRecipient('00000002W', 'Recipient Bussiness 3');

        $invoiceSubject = new InvoiceSubject($emitter, $recipient1, InvoiceSubject::EMITTED_BY_EMITTER);
        $invoiceSubject->addRecipient($recipient2);
        $invoiceSubject->addRecipient($recipient3);

        $this->assertTrue($invoiceSubject->hasMultipleRecipients());
        $this->assertEquals('S', $invoiceSubject->multipleRecipients());
        $this->assertCount(3, $invoiceSubject->recipients());
    }
}

