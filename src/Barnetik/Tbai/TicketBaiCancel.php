<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\CancelInvoice\Header as CancelInvoiceHeader;
use Barnetik\Tbai\CancelInvoice\InvoiceId;
use DOMNode;
use DOMDocument;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Fingerprint\Vendor;

class TicketBaiCancel extends AbstractTicketBai
{
    private Header $header; // Same as SubmitInvoice
    private InvoiceId $invoiceId; // Does not exist on SubmitInvoice
    private Fingerprint $fingerprint; // Without previousInvoice data
    private bool $selfEmployed;

    public function __construct(InvoiceId $invoiceId, Fingerprint $fingerprint, string $territory, bool $selfEmployed = false)
    {
        parent::__construct($territory);
        $this->header = new Header();
        $this->invoiceId = $invoiceId;
        $this->fingerprint = $fingerprint;
        $this->selfEmployed = $selfEmployed;
    }

    public static function createForTicketBai(TicketBai $ticketbai): self
    {
        $header = CancelInvoiceHeader::createForTicketBai($ticketbai);
        $invoiceId = new InvoiceId($ticketbai->issuer(), $header);
        return new self($invoiceId, $ticketbai->fingerprint(), $ticketbai->territory(), $ticketbai->selfEmployed());
    }

    public function issuerVatId(): VatId
    {
        return $this->invoiceId->issuerVatId();
    }

    public function issuerName(): string
    {
        return $this->invoiceId->issuerName();
    }

    public function expeditionDate(): Date
    {
        return $this->invoiceId->expeditionDate();
    }

    public function series(): string
    {
        return $this->invoiceId->series();
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceId->invoiceNumber();
    }

    public function selfEmployed(): bool
    {
        return $this->selfEmployed;
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $tbai = $document->createElementNS('urn:ticketbai:anulacion', 'T:AnulaTicketBai');
        $tbai->appendChild($this->header->xml($document));
        $tbai->appendChild($this->invoiceId->xml($document));
        $tbai->appendChild($this->fingerprint->xml($document));

        $document->appendChild($tbai);
        return $tbai;
    }

    public static function createFromJson(Vendor $vendor, array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $invoiceId = InvoiceId::createFromJson($jsonData['invoiceId']);
        $fingerprint = Fingerprint::createFromJson($vendor, $jsonData['fingerprint'] ?? []);
        $selfEmployed = (bool)($jsonData['self_employed'] ?? false);
        return new TicketBaiCancel($invoiceId, $fingerprint, $territory, $selfEmployed);
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
Faktura baliogabetuko den lurraldea - Territorio en el que se cancelará la factura
  * 01: Araba
  * 02: Bizkaia
  * 03: Gipuzkoa
'
                ],
                'invoiceId' => InvoiceId::docJson(),
                'fingerprint' => Fingerprint::docJson()
            ],
            'required' => ['territory', 'invoiceId', 'fingerprint']
        ];
        return $json;
    }

    public function toArray(): array
    {
        return [
            'territory' => $this->territory,
            'invoiceId' => $this->invoiceId->toArray(),
            'fingerprint' => $this->fingerprint->toArray(),
        ];
    }
}
