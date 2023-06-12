<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Zuzendu\OriginalSignature;
use Barnetik\Tbai\Zuzendu\Header;
use DOMDocument;
use DOMNode;

class Zuzendu extends AbstractTicketBai
{
    private Header $header;
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;
    private OriginalSignature $originalSignature;

    public function __construct(
        Header $header,
        Subject $subject,
        Invoice $invoice,
        Fingerprint $fingerprint,
        OriginalSignature $originalSignature,
        string $territory
    ) {
        parent::__construct($territory);
        $this->header = $header;
        $this->subject = $subject;
        $this->invoice = $invoice;
        $this->fingerprint = $fingerprint;
        $this->originalSignature = $originalSignature;
    }

    public static function createForTicketBai(
        Header $header,
        Subject $subject,
        Invoice $invoice,
        TicketBai $ticketBai
    ): self {
        return new self(
            $header,
            $subject,
            $invoice,
            $ticketBai->fingerprint(),
            new OriginalSignature($ticketBai->signatureValue()),
            $ticketBai->territory()
        );
    }

    public static function createForZuzendu(
        Header $header,
        Subject $subject,
        Invoice $invoice,
        Zuzendu $zuzendu
    ): Zuzendu {
        return new self(
            $header,
            $subject,
            $invoice,
            $zuzendu->fingerprint(),
            $zuzendu->originalSignature(),
            $zuzendu->territory()
        );
    }

    public function fingerprint(): Fingerprint
    {
        return $this->fingerprint;
    }

    public function originalSignature(): OriginalSignature
    {
        return $this->originalSignature;
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $zuzendu = $document->createElementNS('urn:ticketbai:zuzendu-alta', 'T:SubsanacionModificacionTicketBAI');
        $zuzendu->appendChild($this->header->xml($document));
        $zuzendu->appendChild($this->subject->xml($document));
        $zuzendu->appendChild($this->invoice->xml($document));
        $zuzendu->appendChild($this->fingerprint->xml($document));
        $zuzendu->appendChild($this->originalSignature->xml($document));

        $document->appendChild($zuzendu);
        return $zuzendu;
    }

    public static function createFromJson(Vendor $vendor, array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $header = Header::createFromJson($jsonData['header']);
        $subject = Subject::createFromJson($jsonData['subject']);
        $invoice = Invoice::createFromJson($jsonData['invoice']);
        $fingerprint = Fingerprint::createFromJson($vendor, $jsonData['fingerprint'] ?? []);
        $originalSignature = OriginalSignature::createFromJson($jsonData['originalSignature']);

        return new self($header, $subject, $invoice, $fingerprint, $originalSignature, $territory);
    }

    public static function docJson(): array
    {
        return [
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
                'fingerprint' => Fingerprint::docJson(),
                'originalSignature' => OriginalSignature::docJson()
            ],
            'required' => ['territory', 'subject', 'invoice', 'fingerprint', 'originalSignature']
        ];
    }

    public function toArray(): array
    {
        return [
            'territory' => $this->territory,
            'header' => $this->header->toArray(),
            'subject' => $this->subject->toArray(),
            'invoice' => $this->invoice->toArray(),
            'fingerprint' => $this->fingerprint->toArray(),
            'originalSignature' => $this->originalSignature->toArray()
        ];
    }
}
