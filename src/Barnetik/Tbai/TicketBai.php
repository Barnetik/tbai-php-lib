<?php

namespace Barnetik\Tbai;

class TicketBai
{
    private Subject $subject;
    private Invoice $invoice;
    private Fingerprint $fingerprint;

    public function __construct(Subject $subject, Invoice $invoice, Fingerprint $fingerprint)
    {
        $this->subject = $subject;
        $this->invoice = $invoice;
        $this->fingerprint = $fingerprint;
    }

    public function toXml(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        $subject = (string)$this->subject;
        $invoice = (string)$this->invoice;
        $fingerprint = (string)$this->fingerprint;
        return <<<EOF
        <xml>
            <cosas>$subject<cosas>
            <cosas>$invoice<cosas>
            <cosas>$fingerprint<cosas>
        </xml>
EOF;
    }
}
