<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interface\TbaiXml;
use DOMDocument;
use DOMElement;
use DOMNode;
use SimpleXMLElement;

class Header implements TbaiXml
{
    const TBAI_VERSION = '1.2';

    public function xml(DOMDocument $document): DOMNode
    {
        $header = $document->createElement('Cabecera');
        $version = $document->createElement('IDVersionTBAI', self::TBAI_VERSION);
        $header->appendChild($version);
        return $header;
    }
}
