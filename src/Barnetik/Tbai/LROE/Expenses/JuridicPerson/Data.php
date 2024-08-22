<?php

namespace Barnetik\Tbai\LROE\Expenses\JuridicPerson;

use Barnetik\Tbai\LROE\Expenses\Shared\AbstractData;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;

class Data extends AbstractData
{
    private ?Amount $taxableBaseAtCost = null;

    protected static function validVatRegimes(): array
    {
        return [
            self::VAT_REGIME_01,
            self::VAT_REGIME_02,
            self::VAT_REGIME_03,
            self::VAT_REGIME_04,
            self::VAT_REGIME_05,
            self::VAT_REGIME_06,
            self::VAT_REGIME_07,
            self::VAT_REGIME_08,
            self::VAT_REGIME_09,
            self::VAT_REGIME_12,
            self::VAT_REGIME_13,
        ];
    }


    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = parent::xml($domDocument);

        if (isset($this->taxableBaseAtCost) && !is_null($this->taxableBaseAtCost)) {
            $data->appendChild(
                $domDocument->createElement(
                    'BaseImponibleACoste',
                    (string)$this->taxableBaseAtCost
                )
            );
        }

        return $data;
    }

    public static function createFromJson(array $jsonData): static
    {
        $invoiceData = parent::createFromJson($jsonData);

        if (isset($jsonData['taxableBaseAtCost'])) {
            $invoiceData->taxableBaseAtCost = new Amount($jsonData['taxableBaseAtCost']);
        }

        return $invoiceData;
    }

    public static function docJson(): array
    {
        $docJson = parent::docJson();
        $docJson['properties']['taxableBaseAtCost'] = [
            'type' => 'string',
            'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
            'description' => 'Zenbatekoa guztira (2 dezimalekin) - Base imponible a coste (2 decimales)'
        ];

        return $docJson;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if ($this->taxableBaseAtCost) {
            $array['taxableBaseAtCost'] = (string)$this->taxableBaseAtCost;
        }
        return $array;
    }
}
