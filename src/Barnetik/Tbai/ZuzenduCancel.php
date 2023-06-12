<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\CancelInvoice\InvoiceId;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Zuzendu\OriginalSignature;
use Barnetik\Tbai\Zuzendu\Header;
use DOMDocument;
use DOMNode;

class ZuzenduCancel extends AbstractTicketBai
{
    private Header $header;
    private InvoiceId $invoiceId;
    private Fingerprint $fingerprint;
    private OriginalSignature $originalSignature;

    public function __construct(
        InvoiceId $invoiceId,
        Fingerprint $fingerprint,
        OriginalSignature $originalSignature,
        string $territory
    ) {
        parent::__construct($territory);
        $this->header = new Header();
        $this->invoiceId = $invoiceId;
        $this->fingerprint = $fingerprint;
        $this->originalSignature = $originalSignature;
    }

    public static function createForTicketBaiCancel(InvoiceId $invoiceId, TicketBaiCancel $ticketBaiCancel): self
    {
        return new self(
            $invoiceId,
            $ticketBaiCancel->fingerprint(),
            new OriginalSignature($ticketBaiCancel->signatureValue()),
            $ticketBaiCancel->territory()
        );
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $zuzendu = $document->createElementNS('urn:ticketbai:zuzendu-baja', 'T:SubsanacionAnulacionTicketBAI');
        $zuzendu->appendChild($this->header->xml($document));
        $zuzendu->appendChild($this->invoiceId->xml($document));
        $zuzendu->appendChild($this->fingerprint->xml($document));
        $zuzendu->appendChild($this->originalSignature->xmlCancel($document));

        $document->appendChild($zuzendu);
        return $zuzendu;
    }

    public static function createFromJson(Vendor $vendor, array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $invoiceId = InvoiceId::createFromJson($jsonData['invoiceId']);
        $fingerprint = Fingerprint::createFromJson($vendor, $jsonData['fingerprint'] ?? []);
        $originalSignature = OriginalSignature::createFromJson($jsonData['originalSignature']);

        return new self($invoiceId, $fingerprint, $originalSignature, $territory);
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
                'fingerprint' => Fingerprint::docJson(),
                'originalSignature' => OriginalSignature::docJson()
            ],
            'required' => ['territory', 'invoiceId', 'fingerprint', 'originalSignature']
        ];
        return $json;
    }

    public function toArray(): array
    {
        return [
            'territory' => $this->territory,
            'invoiceId' => $this->invoiceId->toArray(),
            'fingerprint' => $this->fingerprint->toArray(),
            'originalSignature' => $this->originalSignature->toArray(),
        ];
    }
}
