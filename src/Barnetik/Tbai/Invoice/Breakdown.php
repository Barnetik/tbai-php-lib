<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Interface\TbaiXml;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use DOMDocument;
use DOMNode;
use OutOfBoundsException;

class Breakdown implements TbaiXml
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

        throw new OutOfBoundsException('Too many not subject breakdown items');
    }

    public function addNationalSubjectExemptBreakdownItem(NationalSubjectExemptBreakdownItem $subjectExemptBreakdowItem): self
    {
        if (sizeof($this->nationalSubjectExemptBreakdownItems) < 7) {
            $this->nationalSubjectExemptBreakdownItems[] = $subjectExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and exempt breakdown items');
    }

    public function addNationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem $subjectNotExemptBreakdowItem): self
    {
        if (sizeof($this->nationalSubjectNotExemptBreakdownItems) < 2) {
            $this->nationalSubjectNotExemptBreakdownItems[] = $subjectNotExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and not exempt breakdown items');
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $breakdown = $domDocument->createElement('TipoDesglose');
        $invoiceBreakdown = $domDocument->createElement('DesgloseFactura');

        if (sizeof($this->nationalNotSubjectBreakdownItems)) {
            $noSubject = $domDocument->createElement('NoSujeta');
            ;
            foreach ($this->nationalNotSubjectBreakdownItems as $nationalNotSubjectItem) {
                $noSubject->appendChild($nationalNotSubjectItem->xml($domDocument));
            }
            $invoiceBreakdown->appendChild($noSubject);
        }


        $breakdown->appendChild($invoiceBreakdown);
        return $breakdown;
    }
}
