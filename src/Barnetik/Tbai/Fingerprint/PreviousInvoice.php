<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;

class PreviousInvoice implements TbaiXml
{
    private string $invoiceNumber;
    private Date $sentDate;
    private string $signature;
    private ?string $sequence;

    public function __construct(string $invoiceNumber, Date $sentDate, string $signature, ?string $sequence)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->sentDate = $sentDate;
        $this->signature = $signature;
        $this->sequence = $sequence;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $previousInvoice = $domDocument->createElement('EncadenamientoFacturaAnterior');
        if ($this->sequence) {
            $previousInvoice->appendChild(
                $domDocument->createElement('SerieFacturaAnterior', $this->sequence)
            );
        }

        $previousInvoice->append(
            $domDocument->createElement('NumFacturaAnterior', $this->invoiceNumber),
            $domDocument->createElement('FechaExpedicionFacturaAnterior', $this->sentDate),
            $domDocument->createElement('SignatureValueFirmaFacturaAnterior', $this->signature)
        );
        return $previousInvoice;
    }
}
