<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interface\TbaiXml;
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
        return $fingerprint;
    }
}
// <element name="EncadenamientoFacturaAnterior" type="T:EncadenamientoFacturaAnteriorType" minOccurs="0"/>
// <element name="Software" type="T:SoftwareFacturacionType"/>
// <element name="NumSerieDispositivo" type="T:TextMax30Type" minOccurs="0"/>
