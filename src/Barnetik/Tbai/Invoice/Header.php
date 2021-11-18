<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidDateException;
use Barnetik\Tbai\Exception\InvalidTimeException;
use DateTime;

class Header
{
    private ?string $series;
    private string $invoiceNumber;
    private string $expeditionDate;
    private string $expeditionTime;
    private bool $isSimplified;

    private function __construct(string $invoiceNumber, string $expeditionDate, string $expeditionTime, ?string $series = null)
    {
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
        if (false === DateTime::createFromFormat("d-m-Y", $expeditionDate)) {
            throw new InvalidDateException('Wrong expedition date provided');
        }

        $this->expeditionDate = $expeditionDate;
        return $this;
    }

    private function setExpeditionTime(string $expeditionTime): self
    {
        if (!preg_match('/\d{2}:\d{2}:\d{2}/', $expeditionTime)) {
            throw new InvalidTimeException('Wrong expedition time provided');
        }

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
}
