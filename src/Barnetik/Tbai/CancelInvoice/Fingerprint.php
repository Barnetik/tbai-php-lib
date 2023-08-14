<?php

namespace Barnetik\Tbai\CancelInvoice;

use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Fingerprint implements TbaiXml
{
    private Vendor $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $fingerprint = $domDocument->createElement('HuellaTBAI');

        $fingerprint->appendChild($this->vendor->xml($domDocument));
        return $fingerprint;
    }

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $vendor = Vendor::createFromXml($xpath, $contextNode);

        return new self($vendor);
    }

    public static function createFromJson(Vendor $vendor, array $jsonData = []): self
    {
        $fingerprint = new Fingerprint($vendor);
        return $fingerprint;
    }

    public static function docJson(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [];
    }

    public function vendor(): Vendor
    {
        return $this->vendor;
    }
}
