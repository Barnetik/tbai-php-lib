<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Fingerprint\PreviousInvoice;
use Barnetik\Tbai\Fingerprint\Vendor;

class Fingerprint
{
    private ?PreviousInvoice $previousInvoice;
    private Vendor $vendor;

    public function __construct(Vendor $vendor, ?PreviousInvoice $previousInvoice = null)
    {
        $this->vendor = $vendor;
        $this->previousInvoice = $previousInvoice;
    }
}
