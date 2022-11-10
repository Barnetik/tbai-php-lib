<?php

namespace Barnetik\Tbai\CancelInvoice;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Header implements TbaiXml
{
    private ?string $series;
    private string $invoiceNumber;
    private Date $expeditionDate;

    private function __construct(string $invoiceNumber, Date $expeditionDate, ?string $series = null)
    {
        $this->series = $series;
        $this->invoiceNumber = $invoiceNumber;
        $this->expeditionDate = $expeditionDate;
    }

    public static function create(string $invoiceNumber, Date $expeditionDate, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $series);
        return $header;
    }

    public static function createForTicketBai(TicketBai $ticketbai): self
    {
        return self::create($ticketbai->invoiceNumber(), $ticketbai->expeditionDate(), $ticketbai->series());
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

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $header = $domDocument->createElement('CabeceraFactura');
        if ($this->series()) {
            $header->appendChild($domDocument->createElement('SerieFactura', htmlspecialchars($this->series(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')));
        }

        $header->appendChild($domDocument->createElement('NumFactura', $this->invoiceNumber()));
        $header->appendChild($domDocument->createElement('FechaExpedicionFactura', $this->expeditionDate()));

        return $header;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $invoiceNumber = $xpath->evaluate('string(/T:AnulaTicketBai/IDFactura/CabeceraFactura/NumFactura)');
        $expeditionDate = new Date($xpath->evaluate('string(/T:AnulaTicketBai/IDFactura/CabeceraFactura/FechaExpedicionFactura)'));
        $series = $xpath->evaluate('string(/T:AnulaTicketBai/IDFactura/CabeceraFactura/SerieFactura)') ?: null;

        return self::create($invoiceNumber, $expeditionDate, $series);
    }

    public static function createFromJson(array $jsonData): self
    {
        $invoiceNumber = $jsonData['invoiceNumber'];
        $expeditionDate = new Date($jsonData['expeditionDate']);
        $series = $jsonData['series'] ?? null;

        return self::create($invoiceNumber, $expeditionDate, $series);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'series' => [
                    'type' => 'string',
                    'maxLength' => 20,
                    'description' => 'Fakturaren seriea - Serie factura'
                ],
                'invoiceNumber' => [
                    'type' => 'string',
                    'maxLength' => 20,
                    'description' => 'Fakturaren zenbakia - Número factura'
                ],
                'expeditionDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Faktura bidali den data (adib: 21-12-2020) - Fecha de expedición de factura (ej: 21-12-2020)'
                ]
            ]
        ];
    }

    public function toArray(): array
    {
        return [
            'series' => $this->series ?? null,
            'invoiceNumber' => $this->invoiceNumber,
            'expeditionDate' => (string)$this->expeditionDate,
        ];
    }
}
