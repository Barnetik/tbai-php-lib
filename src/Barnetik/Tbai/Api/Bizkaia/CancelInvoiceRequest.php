<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Api\ApiRequestInterface;
use DOMDocument;
use DOMElement;
use DOMNode;

class CancelInvoiceRequest implements ApiRequestInterface
{
    const URL = '/N3B4000M/aurkezpena';

    protected string $endpoint;
    protected TicketBaiCancel $ticketbaiCancel;

    private DOMDocument $document;


    public function __construct(TicketBaiCancel $ticketbaiCancel, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->ticketbaiCancel = $ticketbaiCancel;

        $this->document = new DOMDocument('1.0', 'utf-8');
        $rootElement = $this->getRootElement();
        $this->document->appendChild($rootElement);

        $rootElement->appendChild($this->getHeader());
        $rootElement->appendChild($this->getInvoices());
    }

    private function getRootElement(): DOMElement
    {
        if ($this->ticketbaiCancel->selfEmployed()) {
            return $this->document->createElementNS(
                'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PF_140_1_1_Ingresos_ConfacturaConSG_AnulacionPeticion_V1_0_0.xsd',
                'lrpficfcsgap:LROEPF140IngresosConFacturaConSGAnulacionPeticion'
            );
        }
        return $this->document->createElementNS(
            'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AnulacionPeticion_V1_0_0.xsd',
            'lrpjfecsgap:LROEPJ240FacturasEmitidasConSGAnulacionPeticion'
        );
    }

    private function getModel(): string
    {
        if ($this->ticketbaiCancel->selfEmployed()) {
            return '140';
        }

        return '240';
    }

    private function getHeader(): DOMNode
    {
        $header = $this->document->createElement('Cabecera');
        $data = [
            'Modelo' => $this->getModel(),
            'Capitulo' => '1',
            'Subcapitulo' => '1.1',
            'Operacion' => 'AN0',
            'Version' => '1.0',
            'Ejercicio' => date('Y'),
        ];
        foreach ($data as $element => $value) {
            $header->appendChild(
                $this->document->createElement($element, $value)
            );
        }

        $issuer = $this->document->createElement('ObligadoTributario');
        $issuer->appendChild($this->document->createElement('NIF', $this->ticketbaiCancel->issuerVatId()));
        $issuer->appendChild($this->document->createElement('ApellidosNombreRazonSocial', $this->ticketbaiCancel->issuerName()));
        $header->appendChild($issuer);

        return $header;
    }

    private function getInvoices(): DOMElement
    {
        if ($this->ticketbaiCancel->selfEmployed()) {
            return $this->getSelfEmployedInvoices();
        }
        return $this->getLegalPersonInvoices();
    }

    private function getSelfEmployedInvoices(): DOMElement
    {
        $incomes = $this->document->createElement('Ingresos');
        $income = $this->document->createElement('Ingreso');
        $income->appendChild($this->document->createElement('AnulacionTicketBai', $this->ticketbaiCancel->base64Signed()));
        $incomes->appendChild($income);

        return $incomes;
    }

    private function getLegalPersonInvoices(): DOMElement
    {
        $invoices = $this->document->createElement('FacturasEmitidas');
        $invoice = $this->document->createElement('FacturaEmitida');
        $invoice->appendChild($this->document->createElement('AnulacionTicketBai', $this->ticketbaiCancel->base64Signed()));
        $invoices->appendChild($invoice);

        return $invoices;
    }

    public function url(): string
    {
        return $this->endpoint . static::URL;
    }

    public function data(): string
    {
        return gzencode($this->document->saveXML());
    }

    public function jsonDataHeader(): string
    {
        return json_encode([
            'con' => 'LROE',
            'apa' => '1.1',
            'inte' => [
                'nif' => (string)$this->ticketbaiCancel->issuerVatId(),
                'nrs' => $this->ticketbaiCancel->issuerName()
            ],
            'drs' => [
                'mode' => $this->getModel(),
                'ejer' => date('Y')
            ]
        ]);
    }
}
