<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use DOMElement;
use DOMNode;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const URL = '/N3B4000M/aurkezpena';

    private string $endpoint;
    private TicketBai $ticketbai;

    private DOMDocument $document;

    public function __construct(TicketBai $ticketbai, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->ticketbai = $ticketbai;

        $this->document = new DOMDocument('1.0', 'utf-8');
        $rootElement = $this->getRootElement();
        $this->document->appendChild($rootElement);

        $rootElement->appendChild($this->getHeader());
        $rootElement->appendChild($this->getInvoices());
    }

    private function getRootElement(): DOMElement
    {
        if ($this->ticketbai->selfEmployed()) {
            return $this->document->createElementNS(
                'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PF_140_1_1_Ingresos_ConfacturaConSG_AltaPeticion_V1_0_2.xsd',
                'lrpficfcsgap:LROEPF140IngresosConFacturaConSGAltaPeticion'
            );
        }
        return $this->document->createElementNS(
            'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PJ_240_1_1_FacturasEmitidas_ConSG_AltaPeticion_V1_0_2.xsd',
            'lrpjfecsgap:LROEPJ240FacturasEmitidasConSGAltaPeticion'
        );
    }

    private function getModel(): string
    {
        if ($this->ticketbai->selfEmployed()) {
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
        $issuer->appendChild(
            $this->document->createElement(
                'ApellidosNombreRazonSocial',
                htmlspecialchars($this->ticketbai->issuerName(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')
            )
        );

        $header->appendChild($issuer);

        return $header;
    }

    private function getInvoices(): DOMElement
    {
        if ($this->ticketbai->selfEmployed()) {
            return $this->getSelfEmployedInvoices();
        }
        return $this->getLegalPersonInvoices();
    }

    private function getSelfEmployedInvoices(): DOMElement
    {
        $incomes = $this->document->createElement('Ingresos');
        $income = $this->document->createElement('Ingreso');
        $income->appendChild($this->document->createElement('TicketBai', $this->ticketbai->base64Signed()));
        $income->appendChild($this->ticketbai->batuzIncomeTaxes()->xml($this->document));
        $incomes->appendChild($income);
        return $incomes;
    }

    private function getLegalPersonInvoices(): DOMElement
    {
        $invoices = $this->document->createElement('FacturasEmitidas');
        $invoice = $this->document->createElement('FacturaEmitida');
        $invoice->appendChild($this->document->createElement('TicketBai', $this->ticketbai->base64Signed()));
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
                'nif' => (string)$this->ticketbai->issuerVatId(),
                'nrs' => $this->ticketbai->issuerName()
            ],
            'drs' => [
                'mode' => $this->getModel(),
                'ejer' => date('Y')
            ]
        ]);
    }
}
