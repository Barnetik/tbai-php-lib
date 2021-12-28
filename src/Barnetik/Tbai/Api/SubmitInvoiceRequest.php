<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\TicketBai;
use DOMDocument;
use DOMElement;
use DOMNode;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const MODEL = '240';
    const URL = '/N3B4000M/aurkezpena';

    private TicketBai $ticketbai;
    private DOMDocument $document;

    public function __construct(TicketBai $ticketbai)
    {
        $this->ticketbai = $ticketbai;

        $this->document = new DOMDocument('1.0', 'utf-8');
        $rootElement = $this->document->createElementNS(
            'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd',
            'lrpjfecsgap:LROEPJ240FacturasEmitidasConSGAltaPeticion'
        );

        $this->document->appendChild($rootElement);

        $rootElement->appendChild($this->getHeader());
        $rootElement->appendChild($this->getInvoices());
    }

    private function getHeader(): DOMNode
    {
        $header = $this->document->createElement('Cabecera');
        $data = [
            'Modelo' => self::MODEL,
            'Capitulo' => '1',
            'Subcapitulo' => '1.1',
            'Operacion' => 'A00',
            'Version' => '1.0',
            'Ejercicio' => date('Y'),
        ];
        foreach ($data as $element => $value) {
            $header->appendChild(
                $this->document->createElement($element, $value)
            );
        }

        $issuer = $this->document->createElement('ObligadoTributario');
        $issuer->appendChild($this->document->createElement('NIF', $this->ticketbai->issuerVatId()));
        $issuer->appendChild($this->document->createElement('ApellidosNombreRazonSocial', $this->ticketbai->issuerName()));
        $header->appendChild($issuer);

        return $header;
    }

    private function getInvoices(): DOMElement
    {
        $invoices = $this->document->createElement('FacturasEmitidas');
        $invoice = $this->document->createElement('FacturaEmitida');
        $invoice->appendChild($this->document->createElement('TicketBai', $this->ticketbai->base64Signed()));
        $invoices->appendChild($invoice);
        return $invoices;
    }

    public function url(): string
    {
        return self::URL;
    }

    public function data(): string
    {
        return $this->document->saveXML();
    }

    public function jsonDataHeader(): string
    {
        return json_encode([
            'con' => 'LROE',
            'apa' => '1.1',
            'inte' => [
                'nif' => (string)$this->ticketbai->issuerVatId(),
                'nrs' => $this->ticketbai->issuerName()
            ],
            'drs' => [
                'mode' => self::MODEL,
                'ejer' => date('Y')
            ]
        ]);
    }
}
