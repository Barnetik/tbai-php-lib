<?php

namespace Barnetik\Tbai\LROE\Expenses;

use DOMDocument;
use DOMNode;

class JuridicPersonTaxesInfo extends AbstractTaxesInfo
{
    public function xml(DOMDocument $domDocument): DOMNode
    {
        $taxesInfo = $domDocument->createElement('IVA');
        foreach ($this->taxesInfo as $taxInfo) {
            $taxesInfo->appendChild($taxInfo->xml($domDocument));
        }

        return $taxesInfo;
    }

    public static function createFromJson(array $jsonData): self
    {
        $taxesInfo = new self();
        foreach ($jsonData as $taxInfo) {
            $taxesInfo->addTaxInfo(JuridicPersonTaxInfo::createFromJson($taxInfo));
        }
        return $taxesInfo;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'array',
            'items' => JuridicPersonTaxInfo::docJson(),
            'minItems' => 1,
            'maxItems' => 1000,
        ];
    }
}
