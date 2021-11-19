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
}

// <complexType name="NoExentaType">
//     <sequence>
//         <element name="DetalleNoExenta" type="T:DetalleNoExentaType" minOccurs="1" maxOccurs="2"/>
//     </sequence>
// </complexType>
// <complexType name="DetalleNoExentaType">
//     <sequence>
//         <element name="TipoNoExenta" type="T:TipoOperacionSujetaNoExentaType"/>
//         <element name="DesgloseIVA" type="T:DesgloseIVAType"/>
//     </sequence>
// </complexType>
// <complexType name="DesgloseIVAType">
//     <sequence>
//         <element name="DetalleIVA" type="T:DetalleIVAType" maxOccurs="6"/>
//     </sequence>
// </complexType>
// <complexType name="DetalleIVAType">
//     <sequence>
//         <element name="BaseImponible" type="T:ImporteSgn12.2Type"/>
//         <element name="TipoImpositivo" type="T:Tipo3.2Type" minOccurs="0"/>
//         <element name="CuotaImpuesto" type="T:ImporteSgn12.2Type" minOccurs="0"/>
//         <element name="TipoRecargoEquivalencia" type="T:Tipo3.2Type" minOccurs="0"/>
//         <element name="CuotaRecargoEquivalencia" type="T:ImporteSgn12.2Type" minOccurs="0"/>
//         <element name="OperacionEnRecargoDeEquivalenciaORegimenSimplificado" type="T:SiNoType" minOccurs="0"/>
//     </sequence>
// </complexType>
