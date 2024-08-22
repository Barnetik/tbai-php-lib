<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\LROE\Expenses\Interfaces\ExpensesInvoice as InterfacesExpensesInvoice;
use DOMDocument;
use DOMElement;
use DOMNode;

class SubmitExpensesRequest implements ApiRequestInterface
{
    const URL = '/N3B4000M/aurkezpena';

    private string $endpoint;
    private InterfacesExpensesInvoice $expenses;

    private DOMDocument $document;


    public function __construct(InterfacesExpensesInvoice $expenses, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->expenses = $expenses;

        $this->document = new DOMDocument('1.0', 'utf-8');
        $rootElement = $this->getRootElement();
        $this->document->appendChild($rootElement);

        $rootElement->appendChild($this->getHeader());
        $rootElement->appendChild($this->getExpenses());
    }

    private function getRootElement(): DOMElement
    {
        if ($this->expenses->selfEmployed()) {
            return $this->document->createElementNS(
                'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PF_140_2_1_Gastos_Confactura_AltaModifPeticion_V1_0_2.xsd',
                'lrpfgcfamp:LROEPF140GastosConFacturaAltaModifPeticion'
            );
        }

        return $this->document->createElementNS(
            'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PJ_240_2_FacturasRecibidas_AltaModifPeticion_V1_0_1.xsd',
            'lrpjfecsgap:LROEPJ240FacturasRecibidasAltaModifPeticion'
        );
    }

    private function getModel(): string
    {
        if ($this->expenses->selfEmployed()) {
            return '140';
        }

        return '240';
    }

    private function getHeader(): DOMNode
    {
        $header = $this->document->createElement('Cabecera');

        $header->appendChild($this->document->createElement('Modelo', $this->getModel()));
        $header->appendChild($this->document->createElement('Capitulo', '2'));
        if ($this->expenses->selfEmployed()) {
            $header->appendChild($this->document->createElement('Subcapitulo', '2.1'));
        }
        $header->appendChild($this->document->createElement('Operacion', 'A00'));
        $header->appendChild($this->document->createElement('Version', '1.0'));
        $header->appendChild($this->document->createElement('Ejercicio', $this->expenses->receptionDate()->year()));

        $issuer = $this->document->createElement('ObligadoTributario');

        $issuer->appendChild($this->document->createElement('NIF', $this->expenses->recipientVatId()));
        $issuer->appendChild(
            $this->document->createElement(
                'ApellidosNombreRazonSocial',
                htmlspecialchars($this->expenses->recipientName(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8')
            )
        );

        $header->appendChild($issuer);

        return $header;
    }

    private function getExpenses(): DOMElement
    {
        if ($this->expenses->selfEmployed()) {
            return $this->getSelfEmployedExpenses();
        }
        return $this->getLegalPersonExpenses();
    }

    private function getSelfEmployedExpenses(): DOMElement
    {
        $incomes = $this->document->createElement('Gastos');
        $incomes->appendChild($this->expenses->xml($this->document));
        return $incomes;
    }

    private function getLegalPersonExpenses(): DOMElement
    {
        $invoices = $this->document->createElement('FacturasRecibidas');
        $invoices->appendChild($this->expenses->xml($this->document));
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
            'apa' => $this->expenses->selfEmployed() ? '2.1' : '2',
            'inte' => [
                'nif' => (string)$this->expenses->recipientVatId(),
                'nrs' => $this->expenses->recipientName()
            ],
            'drs' => [
                'mode' => $this->getModel(),
                'ejer' => $this->expenses->receptionDate()->year()
            ]
        ]);
    }
}
