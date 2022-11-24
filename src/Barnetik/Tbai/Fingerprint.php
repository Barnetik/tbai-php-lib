<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;

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

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $vendor = Vendor::createFromXml($xpath, $contextNode);

        $previousInvoice = null;
        if ($xpath->evaluate('boolean(HuellaTBAI/EncadenamientoFacturaAnterior)', $contextNode)) {
            $previousInvoice = PreviousInvoice::createFromXml($xpath);
        }

        return new self($vendor, $previousInvoice);
    }

    public static function createFromJson(Vendor $vendor, array $jsonData = []): self
    {
        $previousInvoice = null;
        if (isset($jsonData['previousInvoice'])) {
            $previousInvoice = PreviousInvoice::createFromJson($jsonData['previousInvoice']);
        }

        $fingerprint = new Fingerprint($vendor, $previousInvoice);
        return $fingerprint;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'previousInvoice' => PreviousInvoice::docJson()
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            'previousInvoice' => $this->previousInvoice ? $this->previousInvoice->toArray() : null,
        ];
    }

    public function vendor(): Vendor
    {
        return $this->vendor;
    }
}
