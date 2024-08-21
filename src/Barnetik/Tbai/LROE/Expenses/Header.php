<?php

namespace Barnetik\Tbai\LROE\Expenses;

use Barnetik\Tbai\Header\RectifiedInvoice;
use Barnetik\Tbai\Header\RectifyingInvoice;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;

class Header implements TbaiXml
{
    const INVOICE_TYPE_F1 = 'F1';
    const INVOICE_TYPE_F2 = 'F2';
    const INVOICE_TYPE_F3 = 'F3';
    const INVOICE_TYPE_F4 = 'F4';
    const INVOICE_TYPE_F5 = 'F5';
    const INVOICE_TYPE_F6 = 'F6';
    const INVOICE_TYPE_LC = 'LC';

    private ?string $series;
    private string $invoiceNumber;
    private Date $expeditionDate;
    private Date $receptionDate;
    private ?Date $operationDate = null;
    private string $invoiceType;
    private ?RectifyingInvoice $rectifyingInvoice;

    private array $rectifiedInvoices = [];

    private function __construct(string $invoiceNumber, Date $expeditionDate, Date $receptionDate, string $invoiceType, ?string $series = null)
    {
        $this->series = $series;
        $this->invoiceNumber = $invoiceNumber;
        $this->expeditionDate = $expeditionDate;
        $this->receptionDate = $receptionDate;
        $this->setInvoiceType($invoiceType);
    }

    public static function create(string $invoiceNumber, Date $expeditionDate, Date $receptionDate, string $invoiceType, ?string $series = null): self
    {
        $header = new self($invoiceNumber, $expeditionDate, $receptionDate, $invoiceType, $series);
        return $header;
    }

    public static function createRectifyingInvoice(string $invoiceNumber, Date $expeditionDate, Date $receptionDate, string $invoiceType, RectifyingInvoice $rectifyingInvoice, ?string $series = null): self
    {
        $header = self::create($invoiceNumber, $expeditionDate, $receptionDate, $invoiceType, $series);
        $header->rectifyingInvoice = $rectifyingInvoice;
        return $header;
    }

    private static function validInvoiceTypes(): array
    {
        return [
            static::INVOICE_TYPE_F1,
            static::INVOICE_TYPE_F2,
            static::INVOICE_TYPE_F3,
            static::INVOICE_TYPE_F4,
            static::INVOICE_TYPE_F5,
            static::INVOICE_TYPE_F6,
            static::INVOICE_TYPE_LC,
        ];
    }

    /**
     * @return static
     */
    private function setInvoiceType(string $invoiceType)
    {
        if (!in_array($invoiceType, self::validInvoiceTypes())) {
            throw new InvalidArgumentException('Invoice type is not valid');
        }
        $this->invoiceType = $invoiceType;

        return $this;
    }

    public function series(): ?string
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

    public function receptionDate(): Date
    {
        return $this->receptionDate;
    }

    public function operationDate(): ?Date
    {
        return $this->operationDate;
    }

    public function invoiceType(): string
    {
        return $this->invoiceType;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $header = $domDocument->createElement('CabeceraFactura');
        if ($this->series()) {
            $header->appendChild($domDocument->createElement('SerieFactura', htmlspecialchars($this->series(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')));
        }

        $header->appendChild($domDocument->createElement('NumFactura', $this->invoiceNumber()));
        $header->appendChild($domDocument->createElement('FechaExpedicionFactura', $this->expeditionDate()));

        if ($this->operationDate()) {
            $header->appendChild($domDocument->createElement('FechaOperacion', $this->operationDate()));
        }

        $header->appendChild($domDocument->createElement('FechaRecepcion', $this->receptionDate()));
        $header->appendChild($domDocument->createElement('TipoFactura', $this->invoiceType()));

        if (isset($this->rectifyingInvoice)) {
            $header->appendChild($this->rectifyingInvoice->xml($domDocument));
        }

        if (sizeof($this->rectifiedInvoices)) {
            $rectifiedInvoices = $domDocument->createElement('FacturasRectificadasSustituidas');
            foreach ($this->rectifiedInvoices as $rectifiedInvoice) {
                $rectifiedInvoices->appendChild($rectifiedInvoice->xml($domDocument));
            }
            $header->appendChild($rectifiedInvoices);
        }

        return $header;
    }

    public function addRectifiedInvoice(RectifiedInvoice $rectifiedInvoice): self
    {
        array_push($this->rectifiedInvoices, $rectifiedInvoice);
        return $this;
    }

    public static function createFromJson(array $jsonData): self
    {
        $header = self::createHeaderFromJson($jsonData);

        if (isset($jsonData['rectifiedInvoices']) && $jsonData['rectifiedInvoices']) {
            foreach ($jsonData['rectifiedInvoices'] as $jsonRectifiedInvoice) {
                $jsonRectifiedInvoice = RectifiedInvoice::createFromJson($jsonRectifiedInvoice);
                $header->addRectifiedInvoice($jsonRectifiedInvoice);
            }
        }

        return $header;
    }

    private static function createHeaderFromJson(array $jsonData): self
    {
        $header = self::auxCreateHeaderFromJson($jsonData);
        if (isset($jsonData['operationDate']) && $jsonData['operationDate']) {
            $header->operationDate = new Date($jsonData['operationDate']);
        }
        return $header;
    }

    private static function auxCreateHeaderFromJson(array $jsonData): self
    {
        $invoiceNumber = $jsonData['invoiceNumber'];
        $expeditionDate = new Date($jsonData['expeditionDate']);
        $receptionDate = new Date($jsonData['receptionDate']);
        $series = $jsonData['series'] ?? null;
        $invoiceType = $jsonData['invoiceType'];

        if (isset($jsonData['rectifyingInvoice']) && $jsonData['rectifyingInvoice']) {
            $rectifyingInvoice = RectifyingInvoice::createFromJson($jsonData['rectifyingInvoice']);
            return self::createRectifyingInvoice($invoiceNumber, $expeditionDate, $receptionDate, $invoiceType, $rectifyingInvoice, $series);
        }

        return self::create($invoiceNumber, $expeditionDate, $receptionDate, $invoiceType, $series);
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
                    'minLength' => 1,
                    'maxLength' => 20,
                    'description' => 'Fakturaren zenbakia - Número factura'
                ],
                'expeditionDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Faktura bidali den data (adib: 21-12-2020) - Fecha de expedición de factura (ej: 21-12-2020)'
                ],
                'receptionDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Faktura jaso den data (adib: 21-12-2020) - Fecha de recepción de la factura (ej: 21-12-2020)'
                ],
                'operationDate' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 10,
                    'default' => null,
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Fakturaren eragiketa data (adib: 21-12-2020) - Fecha de operación de la factura (ej: 21-12-2020)'
                ],
                'invoiceType' => [
                    'type' => 'string',
                    'enum' => self::validInvoiceTypes(),
                    'description' => '
Faktura mota - Tipo de factura:
  * F1: Factura con identificación del destinatario o de la destinataria
  * F2: Factura sin identificación del destinatario o de la destinataria
  * F3: Factura emitida en sustitución de facturas simplificadas y declaradas con anterioridad
  * F4: Asiento resumen de facturas
  * F5: Importaciones con DUA
  * F6: Otros justificantes
  * LC: Aduanas-Liquidación complementaria
'
                ],
                'rectifyingInvoice' => RectifyingInvoice::docJson(),
                'rectifiedInvoices' => [
                    'type' => 'array',
                    'items' => RectifiedInvoice::docJson(),
                    'minItems' => 0,
                    'maxItems' => 100,
                ],
            ],
            'required' => ['invoiceNumber', 'expeditionDate', 'receptionDate', 'invoiceType']
        ];
    }

    public function toArray(): array
    {
        return [
            'series' => $this->series ?? null,
            'invoiceNumber' => $this->invoiceNumber,
            'expeditionDate' => (string)$this->expeditionDate,
            'receptionDate' => (string)$this->receptionDate,
            'operationDate' => isset($this->operationDate) ? (string) $this->operationDate : null,
            'invoiceType' => $this->invoiceType,
            'rectifyingInvoice' => isset($this->rectifyingInvoice) ? $this->rectifyingInvoice->toArray() : null,
            'rectifiedInvoices' => array_map(function ($rectifiedInvoice) {
                return $rectifiedInvoice->toArray();
            }, $this->rectifiedInvoices),
        ];
    }
}
