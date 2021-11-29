<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use Barnetik\Tbai\Xades\TicketBai as XadesTicketBai;
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
    private Header $header;
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;

    private ?XAdES $signedXml = null;
    private ?string $signedXmlPath = null;

    public function __construct(Subject $subject, Invoice $invoice, Fingerprint $fingerprint)
    {
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
        $tbai = $document->createElementNS('urn:ticketbai:emision', 'TicketBai');
        // $tbai = $document->createElement('TicketBai');
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

            $this->signedXml = XadesTicketBai::signDocument(
                new InputResourceInfo(
                    $this->dom()->C14N(true, false), // The source document
                    ResourceInfo::string, // The source is a url
                    dirname($signedFilePath), // The location to save the signed document
                    basename($signedFilePath), //$storeFilename, // The name of the file to save the signed document in,
                    null,
                    false
                ),
                new CertificateResourceInfo($certData['cert'], ResourceInfo::string | ResourceInfo::pem),
                new KeyResourceInfo($certData['pkey'], ResourceInfo::string | ResourceInfo::pem),
            );
            $this->signedXmlPath = $signedFilePath;
        }
    }

    public function base64Signed(): string
    {
        return base64_encode(file_get_contents($this->signedXmlPath()));
    }

    public function shortSignatureValue(): string
    {
        $simpleXml = new SimpleXMLElement(file_get_contents($this->signedXmlPath));
        return substr($simpleXml->Signature->SignatureValue, 0, 13);
    }

    public function signedXmlPath(): string
    {
        return $this->signedXmlPath;
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
        $subject = Subject::createFromJson($jsonData['subject']);
        $invoice = Invoice::createFromJson($jsonData['invoice']);
        $fingerprint = Fingerprint::createFromJson($jsonData['fingerprint']);
        $ticketBai = new TicketBai($subject, $invoice, $fingerprint);
        return $ticketBai;
    }

    public static function docJson(): array
    {
        $json = [
            'type' => 'object',
            'properties' => [
                'subject' => Subject::docJson(),
                'invoice' => Invoice::docJson(),
                'fingerprint' => Fingerprint::docJson()
            ],
            'required' => ['subject', 'invoice', 'fingerprint']
        ];
        return $json;
    }
}
