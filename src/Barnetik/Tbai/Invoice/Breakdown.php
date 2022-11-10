<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Invoice\Breakdown\ForeignDeliveryNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\ForeignDeliverySubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\ForeignDeliverySubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\ForeignServiceNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\ForeignServiceSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\ForeignServiceSubjectNotExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalNotSubjectBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectExemptBreakdownItem;
use Barnetik\Tbai\Invoice\Breakdown\NationalSubjectNotExemptBreakdownItem;
use DOMDocument;
use DOMNode;
use DOMXPath;
use OutOfBoundsException;

class Breakdown implements TbaiXml
{
    private array $nationalNotSubjectBreakdownItems = [];
    private array $nationalSubjectExemptBreakdownItems = [];
    private array $nationalSubjectNotExemptBreakdownItems = [];

    private array $foreignServiceNotSubjectBreakdownItems = [];
    private array $foreignServiceSubjectExemptBreakdownItems = [];
    private array $foreignServiceSubjectNotExemptBreakdownItems = [];

    private array $foreignDeliveryNotSubjectBreakdownItems = [];
    private array $foreignDeliverySubjectExemptBreakdownItems = [];
    private array $foreignDeliverySubjectNotExemptBreakdownItems = [];

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

    public function addForeignServiceNotSubjectBreakdownItem(ForeignServiceNotSubjectBreakdownItem $notSubjectBreakdowItem): self
    {
        if (sizeof($this->foreignServiceNotSubjectBreakdownItems) < 2) {
            $this->foreignServiceNotSubjectBreakdownItems[] = $notSubjectBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many not subject breakdown items');
    }

    public function addForeignServiceSubjectExemptBreakdownItem(ForeignServiceSubjectExemptBreakdownItem $subjectExemptBreakdowItem): self
    {
        if (sizeof($this->foreignServiceSubjectExemptBreakdownItems) < 7) {
            $this->foreignServiceSubjectExemptBreakdownItems[] = $subjectExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and exempt breakdown items');
    }

    public function addForeignServiceSubjectNotExemptBreakdownItem(ForeignServiceSubjectNotExemptBreakdownItem $subjectNotExemptBreakdowItem): self
    {
        if (sizeof($this->foreignServiceSubjectNotExemptBreakdownItems) < 2) {
            $this->foreignServiceSubjectNotExemptBreakdownItems[] = $subjectNotExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and not exempt breakdown items');
    }

    public function addForeignDeliveryNotSubjectBreakdownItem(ForeignDeliveryNotSubjectBreakdownItem $notSubjectBreakdowItem): self
    {
        if (sizeof($this->foreignDeliveryNotSubjectBreakdownItems) < 2) {
            $this->foreignDeliveryNotSubjectBreakdownItems[] = $notSubjectBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many not subject breakdown items');
    }

    public function addForeignDeliverySubjectExemptBreakdownItem(ForeignDeliverySubjectExemptBreakdownItem $subjectExemptBreakdowItem): self
    {
        if (sizeof($this->foreignDeliverySubjectExemptBreakdownItems) < 7) {
            $this->foreignDeliverySubjectExemptBreakdownItems[] = $subjectExemptBreakdowItem;
            return $this;
        }

        throw new OutOfBoundsException('Too many subject and exempt breakdown items');
    }

    public function addForeignDeliverySubjectNotExemptBreakdownItem(ForeignDeliverySubjectNotExemptBreakdownItem $subjectNotExemptBreakdowItem): self
    {
        if (sizeof($this->foreignDeliverySubjectNotExemptBreakdownItems) < 2) {
            $this->foreignDeliverySubjectNotExemptBreakdownItems[] = $subjectNotExemptBreakdowItem;
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

        $foreignServiceNotSubjectBreakdownItems = $jsonData['foreignServiceNotSubjectBreakdownItems'] ?? [];
        foreach ($foreignServiceNotSubjectBreakdownItems as $foreignServiceNotSubjectBreakdownItem) {
            $breakdown->addForeignServiceNotSubjectBreakdownItem(ForeignServiceNotSubjectBreakdownItem::createFromJson($foreignServiceNotSubjectBreakdownItem));
        }

        $foreignServiceSubjectExemptBreakdownItems = $jsonData['foreignServiceSubjectExemptBreakdownItems'] ?? [];
        foreach ($foreignServiceSubjectExemptBreakdownItems as $foreignServiceSubjectExemptBreakdownItem) {
            $breakdown->addForeignServiceSubjectExemptBreakdownItem(ForeignServiceSubjectExemptBreakdownItem::createFromJson($foreignServiceSubjectExemptBreakdownItem));
        }

        $foreignServiceSubjectNotExemptBreakdownItems = $jsonData['foreignServiceSubjectNotExemptBreakdownItems'] ?? [];
        foreach ($foreignServiceSubjectNotExemptBreakdownItems as $foreignServiceSubjectNotExemptBreakdownItem) {
            $breakdown->addForeignServiceSubjectNotExemptBreakdownItem(ForeignServiceSubjectNotExemptBreakdownItem::createFromJson($foreignServiceSubjectNotExemptBreakdownItem));
        }

        $foreignDeliveryNotSubjectBreakdownItems = $jsonData['foreignDeliveryNotSubjectBreakdownItems'] ?? [];
        foreach ($foreignDeliveryNotSubjectBreakdownItems as $foreignDeliveryNotSubjectBreakdownItem) {
            $breakdown->addForeignDeliveryNotSubjectBreakdownItem(ForeignDeliveryNotSubjectBreakdownItem::createFromJson($foreignDeliveryNotSubjectBreakdownItem));
        }

        $foreignDeliverySubjectExemptBreakdownItems = $jsonData['foreignDeliverySubjectExemptBreakdownItems'] ?? [];
        foreach ($foreignDeliverySubjectExemptBreakdownItems as $foreignDeliverySubjectExemptBreakdownItem) {
            $breakdown->addForeignDeliverySubjectExemptBreakdownItem(ForeignDeliverySubjectExemptBreakdownItem::createFromJson($foreignDeliverySubjectExemptBreakdownItem));
        }

        $foreignDeliverySubjectNotExemptBreakdownItems = $jsonData['foreignDeliverySubjectNotExemptBreakdownItems'] ?? [];
        foreach ($foreignDeliverySubjectNotExemptBreakdownItems as $foreignDeliverySubjectNotExemptBreakdownItem) {
            $breakdown->addForeignDeliverySubjectNotExemptBreakdownItem(ForeignDeliverySubjectNotExemptBreakdownItem::createFromJson($foreignDeliverySubjectNotExemptBreakdownItem));
        }

        return $breakdown;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $breakdown = new self();

        $nationalSubjectExemptBreakdown = $xpath->query('/T:TicketBai/Factura/TipoDesglose/DesgloseFactura/Sujeta/Exenta/DetalleExenta');
        foreach ($nationalSubjectExemptBreakdown as $node) {
            $nationalSubjectExemptBreakdownItem = NationalSubjectExemptBreakdownItem::createFromXml($xpath, $node);
            $breakdown->addNationalSubjectExemptBreakdownItem($nationalSubjectExemptBreakdownItem);
        }

        $nationalSubjectNotExemptBreakdown = $xpath->query('/T:TicketBai/Factura/TipoDesglose/DesgloseFactura/Sujeta/NoExenta/DetalleNoExenta');
        foreach ($nationalSubjectNotExemptBreakdown as $node) {
            $nationalSubjectNotExemptBreakdownItem = NationalSubjectNotExemptBreakdownItem::createFromXml($xpath, $node);
            $breakdown->addNationalSubjectNotExemptBreakdownItem($nationalSubjectNotExemptBreakdownItem);
        }

        $nationalNotSubjectBreakdown  = $xpath->query('/T:TicketBai/Factura/TipoDesglose/DesgloseFactura/NoSujeta/DetalleNoSujeta');
        foreach ($nationalNotSubjectBreakdown as $node) {
            $nationalNotSubjectBreakdownItem = NationalNotSubjectBreakdownItem::createFromXml($xpath, $node);
            $breakdown->addNationalNotSubjectBreakdownItem($nationalNotSubjectBreakdownItem);
        }

        return $breakdown;
    }

    private function hasNationalBreakdown(): bool
    {
        return sizeof($this->nationalSubjectExemptBreakdownItems) || sizeof($this->nationalSubjectNotExemptBreakdownItems) || sizeof($this->nationalNotSubjectBreakdownItems);
    }

    private function hasForeignServiceBreakdown(): bool
    {
        return sizeof($this->foreignServiceSubjectExemptBreakdownItems)
            || sizeof($this->foreignServiceSubjectNotExemptBreakdownItems)
            || sizeof($this->foreignServiceNotSubjectBreakdownItems);
    }

    private function hasForeignDeliveryBreakdown(): bool
    {
        return sizeof($this->foreignDeliverySubjectExemptBreakdownItems)
        || sizeof($this->foreignDeliverySubjectNotExemptBreakdownItems)
        || sizeof($this->foreignDeliveryNotSubjectBreakdownItems);
    }

    private function hasForeignBreakdown(): bool
    {
        return $this->hasForeignServiceBreakdown() || $this->hasForeignDeliveryBreakdown();
    }

    private function nationalBreakdownXml(DOMDocument $domDocument): DOMNode
    {
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

        return $invoiceBreakdown;
    }

    private function foreignServiceBreakdownXml(DOMDocument $domDocument): DOMNode
    {
        $invoiceBreakdown = $domDocument->createElement('PrestacionServicios');

        if (sizeof($this->foreignServiceSubjectExemptBreakdownItems) || sizeof($this->foreignServiceSubjectNotExemptBreakdownItems)) {
            $subject = $domDocument->createElement('Sujeta');

            if (sizeof($this->foreignServiceSubjectExemptBreakdownItems)) {
                $exempt = $domDocument->createElement('Exenta');
                $subject->appendChild($exempt);

                foreach ($this->foreignServiceSubjectExemptBreakdownItems as $foreignServiceSubjectExempt) {
                    $exempt->appendChild($foreignServiceSubjectExempt->xml($domDocument));
                }
            }

            if (sizeof($this->foreignServiceSubjectNotExemptBreakdownItems)) {
                $notExempt = $domDocument->createElement('NoExenta');
                $subject->appendChild($notExempt);

                foreach ($this->foreignServiceSubjectNotExemptBreakdownItems as $foreignServiceSubjectNotExempt) {
                    $notExempt->appendChild($foreignServiceSubjectNotExempt->xml($domDocument));
                }
            }

            $invoiceBreakdown->appendChild($subject);
        }

        if (sizeof($this->foreignServiceNotSubjectBreakdownItems)) {
            $noSubject = $domDocument->createElement('NoSujeta');

            foreach ($this->foreignServiceNotSubjectBreakdownItems as $foreignServiceNotSubjectItem) {
                $noSubject->appendChild($foreignServiceNotSubjectItem->xml($domDocument));
            }
            $invoiceBreakdown->appendChild($noSubject);
        }

        return $invoiceBreakdown;
    }

    private function foreignDeliveryBreakdownXml(DOMDocument $domDocument): DOMNode
    {
        $invoiceBreakdown = $domDocument->createElement('Entrega');

        if (sizeof($this->foreignDeliverySubjectExemptBreakdownItems) || sizeof($this->foreignDeliverySubjectNotExemptBreakdownItems)) {
            $subject = $domDocument->createElement('Sujeta');

            if (sizeof($this->foreignDeliverySubjectExemptBreakdownItems)) {
                $exempt = $domDocument->createElement('Exenta');
                $subject->appendChild($exempt);

                foreach ($this->foreignDeliverySubjectExemptBreakdownItems as $foreignDeliverySubjectExempt) {
                    $exempt->appendChild($foreignDeliverySubjectExempt->xml($domDocument));
                }
            }

            if (sizeof($this->foreignDeliverySubjectNotExemptBreakdownItems)) {
                $notExempt = $domDocument->createElement('NoExenta');
                $subject->appendChild($notExempt);

                foreach ($this->foreignDeliverySubjectNotExemptBreakdownItems as $foreignDeliverySubjectNotExempt) {
                    $notExempt->appendChild($foreignDeliverySubjectNotExempt->xml($domDocument));
                }
            }

            $invoiceBreakdown->appendChild($subject);
        }

        if (sizeof($this->foreignDeliveryNotSubjectBreakdownItems)) {
            $noSubject = $domDocument->createElement('NoSujeta');

            foreach ($this->foreignDeliveryNotSubjectBreakdownItems as $foreignDeliveryNotSubjectItem) {
                $noSubject->appendChild($foreignDeliveryNotSubjectItem->xml($domDocument));
            }
            $invoiceBreakdown->appendChild($noSubject);
        }

        return $invoiceBreakdown;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $breakdown = $domDocument->createElement('TipoDesglose');

        if ($this->hasNationalBreakdown()) {
            $breakdown->appendChild($this->nationalBreakdownXml($domDocument));
        } else if ($this->hasForeignBreakdown()) {
            $foreignBreakdown = $domDocument->createElement('DesgloseTipoOperacion');
            if ($this->hasForeignServiceBreakdown()) {
                $foreignBreakdown->appendChild($this->foreignServiceBreakdownXml($domDocument));
            }
            if ($this->hasForeignDeliveryBreakdown()) {
                $foreignBreakdown->appendChild($this->foreignDeliveryBreakdownXml($domDocument));
            }
            $breakdown->appendChild($foreignBreakdown);
        }

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
                ],
                'foreignServiceSubjectExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 2,
                    'items' => ForeignServiceSubjectExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsitakoak - Sujetas a carga y exentas'
                ],
                'foreignServiceSubjectNotExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => ForeignServiceSubjectNotExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas'
                ],
                'foreignServiceNotSubjectBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => ForeignServiceNotSubjectBreakdownItem::docJson(),
                    'description' => 'Kargapean ez daudenak - No sujetas a carga'
                ],
                'foreignDeliverySubjectExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 2,
                    'items' => ForeignDeliverySubjectExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsitakoak - Sujetas a carga y exentas'
                ],
                'foreignDeliverySubjectNotExemptBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => ForeignDeliverySubjectNotExemptBreakdownItem::docJson(),
                    'description' => 'Kargapean eta salbuetsi gabe - Sujetas a carga y no exentas'
                ],
                'foreignDeliveryNotSubjectBreakdownItems' => [
                    'type' => 'array',
                    'maxItems' => 7,
                    'items' => ForeignDeliveryNotSubjectBreakdownItem::docJson(),
                    'description' => 'Kargapean ez daudenak - No sujetas a carga'
                ]
            ]

        ];
    }

    public function toArray(): array
    {
        return [
            'nationalSubjectExemptBreakdownItems' => array_map(function ($nationalSubjectExemptBreakdownItem) {
                return $nationalSubjectExemptBreakdownItem->toArray();
            }, $this->nationalSubjectExemptBreakdownItems),
            'nationalSubjectNotExemptBreakdownItems' => array_map(function ($nationalSubjectNotExemptBreakdownItem) {
                return $nationalSubjectNotExemptBreakdownItem->toArray();
            }, $this->nationalSubjectNotExemptBreakdownItems),
            'nationalNotSubjectBreakdownItems' => array_map(function ($nationalNotSubjectBreakdownItem) {
                return $nationalNotSubjectBreakdownItem->toArray();
            }, $this->nationalNotSubjectBreakdownItems),
            'foreignDeliverySubjectExemptBreakdownItems' => array_map(function ($foreignDeliverySubjectExemptBreakdownItem) {
                return $foreignDeliverySubjectExemptBreakdownItem->toArray();
            }, $this->foreignDeliverySubjectExemptBreakdownItems),
            'foreignDeliverySubjectNotExemptBreakdownItems' => array_map(function ($foreignDeliverySubjectNotExemptBreakdownItem) {
                return $foreignDeliverySubjectNotExemptBreakdownItem->toArray();
            }, $this->foreignDeliverySubjectNotExemptBreakdownItems),
            'foreignDeliveryNotSubjectBreakdownItems' => array_map(function ($foreignDeliveryNotSubjectBreakdownItem) {
                return $foreignDeliveryNotSubjectBreakdownItem->toArray();
            }, $this->foreignDeliveryNotSubjectBreakdownItems),
            'foreignServiceSubjectExemptBreakdownItems' => array_map(function ($foreignServiceSubjectExemptBreakdownItem) {
                return $foreignServiceSubjectExemptBreakdownItem->toArray();
            }, $this->foreignServiceSubjectExemptBreakdownItems),
            'foreignServiceSubjectNotExemptBreakdownItems' => array_map(function ($foreignServiceSubjectNotExemptBreakdownItem) {
                return $foreignServiceSubjectNotExemptBreakdownItem->toArray();
            }, $this->foreignServiceSubjectNotExemptBreakdownItems),
            'foreignServiceNotSubjectBreakdownItems' => array_map(function ($foreignServiceNotSubjectBreakdownItem) {
                return $foreignServiceNotSubjectBreakdownItem->toArray();
            }, $this->foreignServiceNotSubjectBreakdownItems),
        ];
    }
}
