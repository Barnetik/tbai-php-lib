<?php

namespace Barnetik\Tbai\Fingerprint;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Date;
use DOMDocument;
use DOMNode;
use DOMXPath;

class PreviousInvoice implements TbaiXml
{
    private string $invoiceNumber;
    private Date $sentDate;
    private string $signature;
    private ?string $series;

    public function __construct(string $invoiceNumber, Date $sentDate, string $signature, ?string $series)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->sentDate = $sentDate;
        $this->signature = $signature;
        $this->series = $series;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $previousInvoice = $domDocument->createElement('EncadenamientoFacturaAnterior');
        if ($this->series) {
            $previousInvoice->appendChild(
                $domDocument->createElement('SerieFacturaAnterior', htmlspecialchars($this->series, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
            );
        }

        $previousInvoice->appendChild($domDocument->createElement('NumFacturaAnterior', $this->invoiceNumber));
        $previousInvoice->appendChild($domDocument->createElement('FechaExpedicionFacturaAnterior', $this->sentDate));
        $previousInvoice->appendChild($domDocument->createElement('SignatureValueFirmaFacturaAnterior', $this->signature));

        return $previousInvoice;
    }

    public static function createFromXml(DOMXPath $xpath): self
    {
        $series = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/EncadenamientoFacturaAnterior/SerieFacturaAnterior)');
        $number = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/EncadenamientoFacturaAnterior/NumFacturaAnterior)');
        $date = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/EncadenamientoFacturaAnterior/FechaExpedicionFacturaAnterior)');
        $signature = $xpath->evaluate('string(/T:TicketBai/HuellaTBAI/EncadenamientoFacturaAnterior/SignatureValueFirmaFacturaAnterior)');

        return new self($number, new Date($date), $signature, $series ?: null);
    }

    public static function createFromJson(array $jsonData): self
    {
        $previousInvoice = new PreviousInvoice($jsonData['invoiceNumber'], new Date($jsonData['sentDate']), $jsonData['signature'], $jsonData['serie'] ?? null);
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
                    'description' => 'Aurreko fakturaren zenbakia - Número factura factura anterior'
                ],
                'sentDate' => [
                    'type' => 'string',
                    'pattern' => '^\d{2,2}-\d{2,2}-\d{4,4}$',
                    'description' => 'Aurreko faktura bidali zen data (adib: 21-12-2020) - Fecha de expedición de factura anterior (ej: 21-12-2020)'
                ],
                'signature' => [
                    'type' => 'string',
                    'maxLength' => 100,
                    'description' => 'Aurreko fakturaren TBAI fitxategiko SignatureValue eremuko lehen ehun karaktereak - Primeros cien caracteres del campo SignatureValue del fichero TBAI de la factura anterior'
                ],
                'serie' => [
                    'type' => 'string',
                    'maxLength' => 20,
                    'description' => 'Aurreko fakturaren seriea - Serie factura anterior'
                ]
            ],
            'required' => ['invoiceNumber', 'sentDate', 'signature']
        ];
    }

    public function toArray(): array
    {
        return [
            'invoiceNumber' => $this->invoiceNumber,
            'sentDate' => (string)$this->sentDate,
            'signature' => $this->signature,
            'serie' => $this->series ?? null,
        ];
    }
}
