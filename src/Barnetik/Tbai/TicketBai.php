<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
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

    private ?string $signedXml = null;

    public function __construct(Subject $subject, Invoice $invoice, Fingerprint $fingerprint)
    {
        $this->header = new Header();
        $this->subject = $subject;
        $this->invoice = $invoice;
        $this->fingerprint = $fingerprint;
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $tbai = $document->createElementNS('urn:ticketbai:emision', 'T:TicketBai');

        // $tbai = $document->createElement('TicketBai');
        $tbai->appendChild($this->header->xml($document));
        $tbai->appendChild($this->subject->xml($document));
        $tbai->appendChild($this->invoice->xml($document));
        $tbai->appendChild($this->fingerprint->xml($document));

        $document->appendChild($tbai);
        return $tbai;
    }

    public function emitterVatId(): VatId
    {
        return $this->subject->emitterVatId();
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

    public function toDom(): DomDocument
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $domNode = $this->xml($xml);
        $xml->append($domNode);
        return $xml;
    }

    public function sign(string $pfxFilePath, string $password, string $storeDir, string $storeFilename): void
    {
        if (!$this->signedXml) {
            openssl_pkcs12_read(
                file_get_contents($pfxFilePath),
                $certData,
                $password
            );

            XAdES::signDocument(
                new InputResourceInfo(
                    $this->toDom()->saveXML(), // The source document
                    ResourceInfo::string, // The source is a url
                    $storeDir, // The location to save the signed document
                    'latest', //$storeFilename, // The name of the file to save the signed document in,
                    null,
                    false
                ),
                new CertificateResourceInfo($certData['cert'], ResourceInfo::string | ResourceInfo::pem),
                new KeyResourceInfo($certData['pkey'], ResourceInfo::string | ResourceInfo::pem),
            );
        }
    }

    public function shortSignatureValue(): string
    {
        $simpleXml = new SimpleXMLElement($this->signedXml);
        return substr($simpleXml->Signature->SignatureValue, 0, 13);
    }

    public function __toString(): string
    {
        return $this->toDom()->saveXml();
    }
}
