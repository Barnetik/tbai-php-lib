<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\CancelInvoice\InvoiceId;
use DOMNode;
use Stringable;
use DOMDocument;
use JsonSerializable;
use SimpleXMLElement;
use lyquidity\xmldsig\XAdES;
use Barnetik\Tbai\ValueObject\Date;
use lyquidity\xmldsig\ResourceInfo;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interfaces\TbaiXml;
use lyquidity\xmldsig\KeyResourceInfo;
use lyquidity\xmldsig\InputResourceInfo;
use Barnetik\Tbai\Xades\Araba as XadesAraba;
use lyquidity\xmldsig\CertificateResourceInfo;
use Barnetik\Tbai\Xades\Bizkaia as XadesBizkaia;
use Barnetik\Tbai\Xades\Gipuzkoa as XadesGipuzkoa;
use Barnetik\Tbai\Exception\InvalidTerritoryException;

class TicketBaiCancel implements Stringable, TbaiXml, JsonSerializable
{
    const TERRITORY_ARABA = '01';
    const TERRITORY_BIZKAIA = '02';
    const TERRITORY_GIPUZKOA = '03';

    private Header $header; // Same as SubmitInvoice
    private InvoiceId $invoiceId; // Does not exist on SubmitInvoice
    private Fingerprint $fingerprint; // Tal cual, sin encadenamientos
    private string $territory;

    private ?XAdES $signedXml = null;
    private ?string $signedXmlPath = null;

    public function __construct(InvoiceId $invoiceId, Fingerprint $fingerprint, string $territory)
    {
        if (!in_array($territory, self::validTerritories())) {
            throw new InvalidTerritoryException();
        }

        $this->territory = $territory;
        $this->header = new Header();
        $this->invoiceId = $invoiceId;
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

    public function territory(): string
    {
        return $this->territory;
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
        $tbai = $document->createElementNS('urn:ticketbai:anulacion', 'T:AnulaTicketBai');
        $tbai->appendChild($this->header->xml($document));
        $tbai->appendChild($this->invoiceId->xml($document));
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

    public function moveSignedXmlTo(string $newPath): void
    {
        rename($this->signedXmlPath, $newPath);
        $this->signedXmlPath = $newPath;
    }

    private function getXadesClassForTerritory(): string
    {
        switch ($this->territory) {
            case self::TERRITORY_ARABA:
                return XadesAraba::class;
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

    public static function createFromJson(Vendor $vendor, array $jsonData): self
    {
        $territory = $jsonData['territory'];
        $invoiceId = InvoiceId::createFromJson($jsonData['invoiceId']);
        $fingerprint = Fingerprint::createFromJson($vendor, $jsonData['fingerprint'] ?? []);
        return new TicketBaiCancel($invoiceId, $fingerprint, $territory);
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

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
