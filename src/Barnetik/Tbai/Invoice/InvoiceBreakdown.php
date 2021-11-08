<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use OutOfBoundsException;

class InvoiceBreakdown
{
    private array $nationalNotSubjectBreakdownItems = [];
    private array $nationalSubjectExemptBreakdownItems = [];
    private array $nationalSubjectNotExemptBreakdownItems = [];

    public function addNationalNotSubjectBreakdownItem(NationalNotSubjectBreakdownItem $notSubjectBreakdowItem): self
    {
        if (sizeof($this->nationalNotSubjectBreakdownItems) < 2) {
            $this->nationalNotSubjectBreakdownItems[] = $notSubjectBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many not subject breadown items');
    }

    public function addNationalSubjectExemptBreakdownItem(NationalSubjectExemptBreakdownItem $subjectExemptBreakdowItem): self
    {
        if (sizeof($this->nationalSubjectExemptBreakdownItems) < 7) {
            $this->nationalSubjectExemptBreakdownItems[] = $subjectExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and exempt breadown items');
    }

    public function addNationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem $subjectNotExemptBreakdowItem): self
    {
        if (sizeof($this->nationalSubjectNotExemptBreakdownItems) < 2) {
            $this->nationalSubjectNotExemptBreakdownItems[] = $subjectNotExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and not exempt breadown items');
    }
}
