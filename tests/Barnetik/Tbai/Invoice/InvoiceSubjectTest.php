<?php

namespace Test\Barnetik\Tbai\Invoice;

use Barnetik\Tbai\ValueObject\VatId;
use Test\Barnetik\TestCase;

class InvoiceSubjectTest extends TestCase
{
    public function test_intracomunitary_invoice_uses_IDOTRO_for_recipient_data(): void
    {
        $ticketbai = $this->ticketBaiMother->createBizkaiaTicketBaiForCompanyFromJson(__DIR__ . '/../__files/tbai-intracomunitary-eu-vat-id-recipient.json');
        $dom = simplexml_import_dom($ticketbai->dom());
        $this->assertEquals(VatId::VAT_ID_TYPE_EUVAT, (string)$dom->Sujetos->Destinatarios[0]->IDDestinatario->IDOtro->IDType);
    }
}
