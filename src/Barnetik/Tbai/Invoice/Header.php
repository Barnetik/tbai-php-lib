<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidDateException;
use Barnetik\Tbai\Exception\InvalidTimeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use Barnetik\Tbai\ValueObject\Time;
use DOMDocument;
use DOMNode;

class Header implements TbaiXml
{
    private ?string $series;
    private string $invoiceNumber;
    private Date $expeditionDate;
    private Time $expeditionTime;
    private bool $isSimplified;

    private function __construct(string $invoiceNumber, Date $expeditionDate, Time $expeditionTime, ?string $series = null)
    {
        $this->series = $series;
        $this->invoiceNumber = $invoiceNumber;
        $this->expeditionDate = $expeditionDate;
        $this->expeditionTime = $expeditionTime;
    }

    public static function create(string $invoiceNumber, Date $expeditionDate, Time $expeditionTime, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $expeditionTime, $series);
        $header->isSimplified = false;
        return $header;
    }
    public static function createSimplified(string $invoiceNumber, Date $expeditionDate, Time $expeditionTime, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $expeditionTime, $series);
        $header->isSimplified = true;
        return $header;
    }

    public function series(): string
    {
        return $this->series;
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function expeditionDate(): Date
    {
        return $this->expeditionDate;
    }

    public function expeditionTime(): Time
    {
        return $this->expeditionTime;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $header = $domDocument->createElement('CabeceraFactura');
        if ($this->series()) {
            $header->appendChild($domDocument->createElement('SerieFactura', $this->series()));
        }

        $header->append(
            $domDocument->createElement('NumFactura', $this->series()),
            $domDocument->createElement('FechaExpedicionFactura', $this->expeditionDate()),
            $domDocument->createElement('HoraExpedicionFactura', $this->expeditionTime()),
            $domDocument->createElement('FacturaSimplificada', $this->isSimplified ? 'S' : 'N')
        );


        return $header;
    }
}

    // <complexType name="CabeceraFacturaType">
    //  <sequence>
    //      <element name="FacturaEmitidaSustitucionSimplificada" type="T:SiNoType" minOccurs="0"/>
    //      <element name="FacturaRectificativa" type="T:FacturaRectificativaType" minOccurs="0"/>
    //      <element name="FacturasRectificadasSustituidas" type="T:FacturasRectificadasSustituidasType" minOccurs="0"/>
    //  </sequence>
    // </complexType>
