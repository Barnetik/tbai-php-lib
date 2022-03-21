<?php

namespace Barnetik\Tbai\CancelInvoice;

use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

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
}
