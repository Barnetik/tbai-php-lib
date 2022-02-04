<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Exception\InvalidTerritoryException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Xades\Bizkaia as XadesBizkaia;
use Barnetik\Tbai\Xades\Gipuzkoa as XadesGipuzkoa;
use DOMDocument;
use DOMNode;
use lyquidity\xmldsig\CertificateResourceInfo;
use lyquidity\xmldsig\InputResourceInfo;
use lyquidity\xmldsig\KeyResourceInfo;
use lyquidity\xmldsig\ResourceInfo;
use lyquidity\xmldsig\XAdES;
use SimpleXMLElement;
use Stringable;

class TicketBai implements Stringable, TbaiXml
{
    const TERRITORY_ARABA = '01';
    const TERRITORY_BIZKAIA = '02';
    const TERRITORY_GIPUZKOA = '03';

    private Header $header;
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;
    private string $territory;

    private ?XAdES $signedXml = null;
    private ?string $signedXmlPath = null;

    public function __construct(Subject $subject, Invoice $invoice, Fingerprint $fingerprint, string $territory)
    {
        if (!in_array($territory, self::validTerritories())) {
            throw new InvalidTerritoryException();
        }

        $this->territory = $territory;
        $this->header = new Header();
        $this->subject = $subject;
        $this->invoice = $invoice;
        $this->fingerprint = $fingerprint;
    }

    private static function validTerritories(): array
    {
        return [
            self::TERRITORY_ARABA,
            self::TERRITORY_BIZKAIA,
            self::TERRITORY_GIPUZKOA,
        ];
    }

    public function issuerVatId(): VatId
    {
        return $this->subject->issuerVatId();
    }

    public function issuerName(): string
    {
        return $this->subject->issuerName();
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

    public function totalAmmount(): Ammount
    {
        return $this->invoice->totalAmmount();
    }

    public function dom(): DomDocument
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $domNode = $this->xml($xml);
        $xml->append($domNode);
        return $xml;
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

    public function sign(string $pfxFilePath, string $password, string $signedFilePath): void
    {
        if (!$this->signedXml) {
            openssl_pkcs12_read(
                file_get_contents($pfxFilePath),
                $certData,
                $password
            );

            $xadesClass = $this->getXadesClassForTerritory();

            $this->signedXml = call_user_func(
                $xadesClass . '::signDocument',
                new InputResourceInfo(
                    $this->dom(), /** @phpstan-ignore-line */
                    ResourceInfo::xmlDocument, // The source is a DOMDocument
                    dirname($signedFilePath), // The location to save the signed document
                    basename($signedFilePath), // The name of the file to save the signed document in,
                    null,
                    false // Enveloped signature
                ),
                new CertificateResourceInfo($certData['cert'], ResourceInfo::string | ResourceInfo::pem),
                new KeyResourceInfo($certData['pkey'], ResourceInfo::string | ResourceInfo::pem)
            );
            $this->signedXmlPath = $signedFilePath;
        }
    }

    private function getXadesClassForTerritory(): string
    {
        switch ($this->territory) {
            case self::TERRITORY_ARABA:
            case self::TERRITORY_GIPUZKOA:
                return XadesGipuzkoa::class;
            case self::TERRITORY_BIZKAIA:
                return XadesBizkaia::class;
            default:
        }
        throw new InvalidTerritoryException();
    }

    public function base64Signed(): string
    {
        return base64_encode(file_get_contents($this->signedXmlPath()));
    }

    public function signatureValue(): string
    {
        $simpleXml = new SimpleXMLElement(file_get_contents($this->signedXmlPath));
        $namespaces = $simpleXml->getNamespaces(true);
        $ds = $simpleXml->children($namespaces['ds']);
        return (string)$ds->Signature->SignatureValue;
    }

    public function shortSignatureValue(): string
    {
        return substr($this->signatureValue(), 0, 13);
    }

    public function signedXmlPath(): string
    {
        return $this->signedXmlPath;
    }

    public function signed(): string
    {
        return file_get_contents($this->signedXmlPath);
    }

    public function isSigned(): bool
    {
        return (bool)$this->signedXmlPath;
    }

    public function __toString(): string
    {
        return $this->dom()->saveXml();
    }

    public static function createFromJson(array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $subject = Subject::createFromJson($jsonData['subject']);
        $invoice = Invoice::createFromJson($jsonData['invoice']);
        $fingerprint = Fingerprint::createFromJson($jsonData['fingerprint']);
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
}
