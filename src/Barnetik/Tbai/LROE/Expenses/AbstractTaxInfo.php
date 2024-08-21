<?php

namespace Barnetik\Tbai\LROE\Expenses;

use DOMNode;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMElement;

abstract class AbstractTaxInfo implements TbaiXml
{
    const TYPE_ENUM_SI_NO = 'SiNoEnum';
    const TYPE_STRING = 'string';

    /** @phpstan-ignore-next-line */
    protected function appendOptionalXml(DOMElement $parent, DOMNode $element, $value, string $type = self::TYPE_STRING): void
    {
        /** @phpstan-ignore-next-line */
        if (isset($value) && !is_null($value)) {
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
