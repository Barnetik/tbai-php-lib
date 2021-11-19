<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\TypeChecker\Date;
use DOMDocument;
use DOMNode;

class PreviousInvoice implements TbaiXml
{
    private string $invoiceNumber;
    private string $sentDate;
    private string $signature;
    private ?string $sequence;

    private Date $dateTypeChecker;

    public function __construct(string $invoiceNumber, string $sentDate, string $signature, ?string $sequence)
    {
        $this->dateTypeChecker = new Date();

        $this->invoiceNumber = $invoiceNumber;
        $this->setSentDate($sentDate);
        $this->signature = $signature;
        $this->sequence = $sequence;
    }

    protected function setSentDate(string $sentDate): self
    {
        $this->dateTypeChecker->check($sentDate);

        $this->sentDate = $sentDate;
        return $this;
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
