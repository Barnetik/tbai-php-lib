<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Subject\Issuer;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

class Subject implements TbaiXml
{
    const ISSUED_BY_ISSUER = 'N';
    const ISSUED_BY_THIRD_PARTY = 'T';
    const ISSUED_BY_RECIPIENT = 'D';

    protected Issuer $issuer;
    protected array $recipients = [];
    protected string $issuedBy;

    public function __construct(Issuer $issuer, Recipient $recipient = null, string $issuedBy = self::ISSUED_BY_ISSUER)
    {
        $this->issuer = $issuer;
        if ($recipient) {
            $this->addRecipient($recipient);
        }

        if (!in_array($issuedBy, self::validIssuedByValues())) {
            throw new InvalidArgumentException('Invalid issuedBy value provided');
        }
        $this->issuedBy = $issuedBy;
    }

    public function addRecipient(Recipient $recipient): self
    {
        array_push($this->recipients, $recipient);
        return $this;
    }

    public function issuer(): Issuer
    {
        return $this->issuer;
    }

    public function recipients(): array
    {
        return $this->recipients;
    }

    public function issuerVatId(): VatId
    {
        return $this->issuer->vatId();
    }

    public function issuerName(): string
    {
        return $this->issuer->name();
    }

    public function multipleRecipients(): string
    {
        if ($this->hasMultipleRecipients()) {
            return 'S';
        }
        return 'N';
    }

    public function hasMultipleRecipients(): bool
    {
        return sizeof($this->recipients) > 1;
    }

    public function issuedBy(): string
    {
        return $this->issuedBy;
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $subject = $document->createElement('Sujetos');
        $subject->appendChild($this->issuer->xml($document));

        if ($this->recipients) {
            $recipients = $document->createElement('Destinatarios');
            foreach ($this->recipients as $recipient) {
                $recipients->appendChild(
                    $recipient->xml($document)
                );
            }
            $subject->appendChild($recipients);
            $subject->appendChild($document->createElement('VariosDestinatarios', $this->multipleRecipients()));
            $subject->appendChild($document->createElement('EmitidaPorTercerosODestinatario', $this->issuedBy()));
        }


        return $subject;
    }

    private static function validIssuedByValues(): array
    {
        return [
            self::ISSUED_BY_ISSUER,
            self::ISSUED_BY_THIRD_PARTY,
            self::ISSUED_BY_RECIPIENT
        ];
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $issuer = Issuer::createFromXml($xpath);
        $isuedBy = $xpath->evaluate('string(/T:TicketBai/Sujetos/EmitidaPorTercerosODestinatario)');
        $subject = new Subject($issuer, null, $isuedBy ?: self::ISSUED_BY_ISSUER);

        $recipients = $xpath->query('/T:TicketBai/Sujetos/Destinatarios/IDDestinatario');
        foreach ($recipients as $recipient) {
            $subject->addRecipient(Recipient::createFromXml($xpath, $recipient));
        }

        return $subject;
    }

    public static function createFromJson(array $jsonData): self
    {
        $issuer = Issuer::createFromJson($jsonData['issuer']);
        $subject = new Subject($issuer, null, $jsonData['issuedBy'] ?? self::ISSUED_BY_ISSUER);

        if (isset($jsonData['recipients'])) {
            foreach ($jsonData['recipients'] as $jsonRecipient) {
                $recipient = Recipient::createFromJson($jsonRecipient);
                $subject->addRecipient($recipient);
            }
        }

        return $subject;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'issuer' => Issuer::docJson(),
                'recipients' => [
                    'type' => 'array',
                    'items' => Recipient::docJson(),
                    'minItems' => 0,
                    'maxItems' => 100
                ],
                'issuedBy' => [
                    'type' => 'string',
                    'enum' => self::validIssuedByValues(),
                    'default' => 'N',
                    'description' => '
Hirugarren batek edo hartzaileak egindako faktura - Factura emitida por tercera entidad o por entidad destinataria
 * N: Ez. Faktura egileak berak egin du - No. Factura emitida por la propia entidad emisora
 * T: Faktura hirugarren batek egin du - Factura emitida por tercera entidad
 * D: Faktura eragiketaren hartzaileak egin du - Factura emitida por la entidad destinataria de la operación
                    ',
                ],
            ],
            'required' => ['issuer', 'recipients']
        ];
    }

    public function toArray(): array
    {
        return [
            'issuer' => $this->issuer->toArray(),
            'recipients' => array_map(function ($recipient) {
                return $recipient->toArray();
            }, $this->recipients),
            'issuedBy' => $this->issuedBy
        ];
    }
}
