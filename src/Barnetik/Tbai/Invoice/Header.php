<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidDateException;
use Barnetik\Tbai\Exception\InvalidTimeException;
use Barnetik\Tbai\Interface\TbaiXml;
use Barnetik\Tbai\TypeChecker\Date;
use Barnetik\Tbai\TypeChecker\Time;
use DateTime;
use DOMDocument;
use DOMNode;

class Header implements TbaiXml
{
    private ?string $series;
    private string $invoiceNumber;
    private string $expeditionDate;
    private string $expeditionTime;
    private bool $isSimplified;

    private Time $timeChecker;
    private Date $dateChecker;

    private function __construct(string $invoiceNumber, string $expeditionDate, string $expeditionTime, ?string $series = null)
    {
        $this->timeChecker = new Time();
        $this->dateChecker = new Date();

        $this->series = $series;
        $this->invoiceNumber = $invoiceNumber;
        $this->setExpeditionDate($expeditionDate);
        $this->setExpeditionTime($expeditionTime);
    }

    public static function create(string $invoiceNumber, string $expeditionDate, string $expeditionTime, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $expeditionTime, $series);
        $header->isSimplified = false;
        return $header;
    }
    public static function createSimplified(string $invoiceNumber, string $expeditionDate, string $expeditionTime, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $expeditionTime, $series);
        $header->isSimplified = true;
        return $header;
    }

    private function setExpeditionDate(string $expeditionDate): self
    {
        $this->dateChecker->check($expeditionDate);

        $this->expeditionDate = $expeditionDate;
        return $this;
    }

    private function setExpeditionTime(string $expeditionTime): self
    {
        $this->timeChecker->check($expeditionTime);

        $this->expeditionTime = $expeditionTime;
        return $this;
    }

    public function series(): string
    {
        return $this->series;
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function expeditionDate(): string
    {
        return $this->expeditionDate;
    }

    public function expeditionTime(): string
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
