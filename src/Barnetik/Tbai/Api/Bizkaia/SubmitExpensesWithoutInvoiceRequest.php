<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\LROE\Expenses\SelfEmployed\ExpensesWithoutInvoice;
use DOMDocument;
use DOMElement;
use DOMNode;

class SubmitExpensesWithoutInvoiceRequest implements ApiRequestInterface
{
    const URL = '/N3B4000M/aurkezpena';

    private string $endpoint;
    private ExpensesWithoutInvoice $expenses;

    private DOMDocument $document;


    public function __construct(ExpensesWithoutInvoice $expenses, string $endpoint)
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
        return $this->document->createElementNS(
            'https://www.batuz.eus/fitxategiak/batuz/LROE/esquemas/LROE_PF_140_2_2_Gastos_Sinfactura_AltaModifPeticion_V1_0_2.xsd',
            'lrpfgsfamp:LROEPF140GastosSinFacturaAltaModifPeticion'
        );
    }

    private function getModel(): string
    {
        return '140';
    }

    private function getHeader(): DOMNode
    {
        $header = $this->document->createElement('Cabecera');

        $header->appendChild($this->document->createElement('Modelo', $this->getModel()));
        $header->appendChild($this->document->createElement('Capitulo', '2'));
        if ($this->expenses->selfEmployed()) {
            $header->appendChild($this->document->createElement('Subcapitulo', '2.2'));
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
        $expenses = $this->document->createElement('Gastos');
        $expenses->appendChild($this->expenses->xml($this->document));
        return $expenses;
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
            'apa' => '2.2',
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
