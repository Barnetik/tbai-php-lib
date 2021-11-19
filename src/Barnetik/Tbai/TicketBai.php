<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use SimpleXMLElement;
use Stringable;

class TicketBai implements Stringable, TbaiXml
{
    private Header $header;
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;

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

    public function toDom(): DomDocument
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $domNode = $this->xml($xml);
        $xml->append($domNode);
        return $xml;
    }

    public function __toString(): string
    {
        return $this->toDom()->saveXml();
    }
}
