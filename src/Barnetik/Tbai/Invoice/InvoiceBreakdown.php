<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use OutOfBoundsException;

class InvoiceBreakdown
{
    private array $nationalNotSubjectBreakdownItems = [];

    public function addNationalNotSubjectBreakdownItem(NationalNotSubjectBreakdownItem $notSubjectBreakdowItem): self
    {
        if (sizeof($this->nationalNotSubjectBreakdownItems) < 2) {
            $this->nationalNotSubjectBreakdownItems[] = $notSubjectBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many not subject breadown items');
    }
}
