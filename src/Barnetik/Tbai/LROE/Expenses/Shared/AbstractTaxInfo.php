<?php

namespace Barnetik\Tbai\LROE\Expenses\Shared;

use DOMNode;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMElement;

abstract class AbstractTaxInfo implements TbaiXml
{
    const TYPE_ENUM_SI_NO = 'SiNoEnum';
    const TYPE_STRING = 'string';

    protected function appendOptionalXml(DOMElement $parent, DOMNode $element, $value, string $type = self::TYPE_STRING): void
    {
        if (isset($value) && $value !== null) {
            if ($type === self::TYPE_STRING) {
                $element->nodeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
            }

            if ($type === self::TYPE_ENUM_SI_NO) {
                if ($value) {
                    $element->nodeValue = 'S';
                } else {
                    $element->nodeValue = 'N';
                }
            }

            $parent->appendChild($element);
        }
    }
}
