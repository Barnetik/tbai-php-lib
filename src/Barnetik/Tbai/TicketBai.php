<?php

namespace Barnetik\Tbai;

use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\Exception\InvalidTerritoryException;
use Barnetik\Tbai\Subject\Issuer;

class TicketBai extends AbstractTicketBai
{
    private Header $header;
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;

    public function __construct(Subject $subject, Invoice $invoice, Fingerprint $fingerprint, string $territory)
    {
        parent::__construct($territory);
        $this->header = new Header();
        $this->subject = $subject;
        $this->invoice = $invoice;
        $this->fingerprint = $fingerprint;
    }

    public function issuerVatId(): VatId
    {
        return $this->subject->issuerVatId();
    }

    public function issuerName(): string
    {
        return $this->subject->issuerName();
    }

    public function issuer(): Issuer
    {
        return $this->subject->issuer();
    }

    public function expeditionDate(): Date
    {
        return $this->invoice->expeditionDate();
    }

    public function series(): string
    {
        return $this->invoice->series();
    }

    public function invoiceNumber(): string
    {
        return $this->invoice->invoiceNumber();
    }

    public function totalAmount(): Amount
    {
        return $this->invoice->totalAmount();
    }

    public function fingerprint(): Fingerprint
    {
        return $this->fingerprint;
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $tbai = $document->createElementNS('urn:ticketbai:emision', 'T:TicketBai');
        $tbai->appendChild($this->header->xml($document));
        $tbai->appendChild($this->subject->xml($document));
        $tbai->appendChild($this->invoice->xml($document));
        $tbai->appendChild($this->fingerprint->xml($document));

        $document->appendChild($tbai);
        return $tbai;
    }

    public static function createFromJson(Vendor $vendor, array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $subject = Subject::createFromJson($jsonData['subject']);
        $invoice = Invoice::createFromJson($jsonData['invoice']);
        $fingerprint = Fingerprint::createFromJson($vendor, $jsonData['fingerprint'] ?? []);
        $ticketBai = new TicketBai($subject, $invoice, $fingerprint, $territory);
        return $ticketBai;
    }

    public static function docJson(): array
    {
        $json = [
            'type' => 'object',
            'properties' => [
                'territory' => [
                    'type' => 'string',
                    'enum' => self::validTerritories(),
                    'description' => '
Faktura aurkeztuko den lurraldea - Territorio en el que se presentará la factura
  * 01: Araba
  * 02: Bizkaia
  * 03: Gipuzkoa
'
                ],
                'subject' => Subject::docJson(),
                'invoice' => Invoice::docJson(),
                'fingerprint' => Fingerprint::docJson()
            ],
            'required' => ['territory', 'subject', 'invoice', 'fingerprint']
        ];
        return $json;
    }

    public function toArray(): array
    {
        return [
            'territory' => $this->territory,
            'subject' => $this->subject->toArray(),
            'invoice' => $this->invoice->toArray(),
            'fingerprint' => $this->fingerprint->toArray(),
        ];
    }
}
