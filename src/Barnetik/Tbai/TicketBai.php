<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;
use Selective\XmlDSig\DigestAlgorithmType;
use Selective\XmlDSig\XmlSigner;
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
        $tbai->append(
            $this->header->xml($document),
            $this->subject->xml($document),
            $this->invoice->xml($document),
            $this->fingerprint->xml($document),
        );

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

    public function sign(string $pfxFilePath, string $password): string
    {
        if (!$this->signedXml) {
            $xmlString = $this->__toString();
            $xmlSigner = new XmlSigner();
            $xmlSigner->loadPfxFile($pfxFilePath, $password);
            $this->signedXml = $xmlSigner->signXml($xmlString, DigestAlgorithmType::SHA512);
        }

        return $this->signedXml;
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
