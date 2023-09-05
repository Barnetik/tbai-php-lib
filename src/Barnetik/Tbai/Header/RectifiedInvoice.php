<?php

namespace Barnetik\Tbai\Header;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;
use DOMXPath;

class RectifiedInvoice implements TbaiXml
{
    private string $invoiceNumber;
    private Date $sentDate;
    private ?string $series;

    public function __construct(string $invoiceNumber, Date $sentDate, ?string $series)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->sentDate = $sentDate;
        $this->series = $series;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $rectifiedInvoice = $domDocument->createElement('IDFacturaRectificadaSustituida');
        if ($this->series) {
            $rectifiedInvoice->appendChild(
                $domDocument->createElement('SerieFactura', htmlspecialchars($this->series, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
            );
        }

        $rectifiedInvoice->appendChild($domDocument->createElement('NumFactura', $this->invoiceNumber));
        $rectifiedInvoice->appendChild($domDocument->createElement('FechaExpedicionFactura', $this->sentDate));

        return $rectifiedInvoice;
    }

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $invoiceNumber = $xpath->evaluate('string(SerieFactura)', $contextNode);
        $sentDate = new Date($xpath->evaluate('string(FechaExpedicionFactura)', $contextNode));
        $series = $xpath->evaluate('string(SerieFactura)', $contextNode) ?: null;

        return new self($invoiceNumber, $sentDate, $series);
    }

    public static function createFromJson(array $jsonData): self
    {
        $series = null;
        if (array_key_exists('series', $jsonData)) {
            $series = $jsonData['series'];
        } else if (array_key_exists('serie', $jsonData)) {
            trigger_error(
                'Deprecated. Avoid "serie" tag on json, "series" should be used instead. Future versions will remove this tag',
                E_USER_DEPRECATED
            );

            $series = $jsonData['serie'];
        }


        $previousInvoice = new RectifiedInvoice($jsonData['invoiceNumber'], new Date($jsonData['sentDate']), $series);
        return $previousInvoice;
    }


    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoiceNumber' => [
                    'type' => 'string',
                    'maxLength' => 20,
                    'description' => 'Zuzendutako edo ordezkatutako faktura identifikatzen duen zenbakia - Número de la factura rectificada o sustituida'
                ],
                'sentDate' => [
                    'type' => 'string',
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Zuzendutako edo ordezkatutako faktura egin den data (adib: 21-12-2020) - Fecha de expedición de la factura rectificada o sustituida (ej: 21-12-2020)'
                ],
                'series' => [
                    'type' => 'string',
                    'maxLength' => 20,
                    'description' => 'Zuzendutako edo ordezkatutako faktura identifikatzen duen serie zenbakia - Número de serie que identifica a la factura rectificada o sustituida'
                ]
            ],
            'required' => ['invoiceNumber', 'sentDate']
        ];
    }

    public function toArray(): array
    {
        return [
            'invoiceNumber' => $this->invoiceNumber,
            'sentDate' => (string)$this->sentDate,
            'serie' => $this->series ?? null,
        ];
    }
}
