<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class Fingerprint implements TbaiXml
{
    private ?PreviousInvoice $previousInvoice;
    private Vendor $vendor;

    public function __construct(Vendor $vendor, ?PreviousInvoice $previousInvoice = null)
    {
        $this->vendor = $vendor;
        $this->previousInvoice = $previousInvoice;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $fingerprint = $domDocument->createElement('HuellaTBAI');

        if ($this->previousInvoice) {
            $fingerprint->appendChild($this->previousInvoice->xml($domDocument));
        }
        $fingerprint->appendChild($this->vendor->xml($domDocument));
        return $fingerprint;
    }

    public function docJson(): array
    {
        return [
            'vendor',
            'previousInvoice'
        ];
    }
}
