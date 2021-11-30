<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Interfaces\TbaiXml;
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

    public static function createFromJson(array $jsonData): self
    {
        $breakdown = new self();

        $nationalSubjectExemptBreakdownItems = $jsonData['nationalSubjectExemptBreakdownItems'] ?? [];
        foreach ($nationalSubjectExemptBreakdownItems as $nationalSubjectExemptBreakdownItem) {
            $breakdown->addNationalSubjectExemptBreakdownItem(NationalSubjectExemptBreakdownItem::createFromJson($nationalSubjectExemptBreakdownItem));
        }

        $nationalSubjectNotExemptBreakdownItems = $jsonData['nationalSubjectNotExemptBreakdownItems'] ?? [];
        foreach ($nationalSubjectNotExemptBreakdownItems as $nationalSubjectNotExemptBreakdownItem) {
            $breakdown->addNationalSubjectNotExemptBreakdownItem(NationalSubjectNotExemptBreakdownItem::createFromJson($nationalSubjectNotExemptBreakdownItem));
        }

        $nationalNotSubjectBreakdownItems = $jsonData['nationalNotSubjectBreakdownItems'] ?? [];
        foreach ($nationalNotSubjectBreakdownItems as $nationalNotSubjectBreakdownItem) {
            $breakdown->addNationalNotSubjectBreakdownItem(NationalNotSubjectBreakdownItem::createFromJson($nationalNotSubjectBreakdownItem));
        }
        return $breakdown;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $breakdown = $domDocument->createElement('TipoDesglose');
        $invoiceBreakdown = $domDocument->createElement('DesgloseFactura');

        if (sizeof($this->nationalSubjectExemptBreakdownItems) || sizeof($this->nationalSubjectNotExemptBreakdownItems)) {
            $subject = $domDocument->createElement('Sujeta');

            if (sizeof($this->nationalSubjectExemptBreakdownItems)) {
                $exempt = $domDocument->createElement('Exenta');
                $subject->appendChild($exempt);

                foreach ($this->nationalSubjectExemptBreakdownItems as $nationalSubjectExempt) {
                    $exempt->appendChild($nationalSubjectExempt->xml($domDocument));
                }
            }

            if (sizeof($this->nationalSubjectNotExemptBreakdownItems)) {
                $notExempt = $domDocument->createElement('NoExenta');
                $subject->appendChild($notExempt);

                foreach ($this->nationalSubjectNotExemptBreakdownItems as $nationalSubjectNotExempt) {
                    $notExempt->appendChild($nationalSubjectNotExempt->xml($domDocument));
                }
            }

            $invoiceBreakdown->appendChild($subject);
        }

        if (sizeof($this->nationalNotSubjectBreakdownItems)) {
            $noSubject = $domDocument->createElement('NoSujeta');

            foreach ($this->nationalNotSubjectBreakdownItems as $nationalNotSubjectItem) {
                $noSubject->appendChild($nationalNotSubjectItem->xml($domDocument));
            }
            $invoiceBreakdown->appendChild($noSubject);
        }


        $breakdown->appendChild($invoiceBreakdown);
        return $breakdown;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'nationalSubjectExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 2,
                    'items' => NationalSubjectExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsitakoak - Sujetas a carga y exentas'
                ],
                'nationalSubjectNotExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => NationalSubjectNotExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas'
                ],
                'nationalNotSubjectBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => NationalNotSubjectBreakdownItem::docJson(),
                    'description' => 'Kargapean ez daudenak - No sujetas a carga'
                ]
            ]

        ];
    }
}
