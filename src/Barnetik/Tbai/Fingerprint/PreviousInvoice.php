<?php

namespace Barnetik\Tbai\Fingerprint;

class PreviousInvoice
{
    private string $invoiceNumber;
    private string $sentDate;
    private string $signature;
    private ?string $sequence;

    public function __construct(string $invoiceNumber, string $sentDate, string $signature, ?string $sequence)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->sentDate = $sentDate;
        $this->signature = $signature;
        $this->sequence = $sequence;
    }
}
