<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;
use DOMDocument;
use DOMNode;

class Invoice implements TbaiXml
{
    private Header $header;
    private Data $data;
    private Breakdown $breakdown;

    public function __construct(Header $header, Data $data, Breakdown $breakdown)
    {
        $this->header = $header;
        $this->data = $data;
        $this->breakdown = $breakdown;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $invoice = $domDocument->createElement('Factura');

        $invoice->append(
            $this->header->xml($domDocument),
            $this->data->xml($domDocument),
            $this->breakdown->xml($domDocument)
        );

        return $invoice;
    }
}

        //  <element name="CabeceraFactura" type="T:CabeceraFacturaType"/>
        //  <element name="DatosFactura" type="T:DatosFacturaType"/>
        //  <element name="TipoDesglose" type="T:TipoDesgloseType"/>
        // </sequence>
