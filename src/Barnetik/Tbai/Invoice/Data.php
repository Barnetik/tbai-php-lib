<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\Exception\InvalidVatRegimeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;
use OutOfBoundsException;

class Data implements TbaiXml
{
    const VAT_REGIME_01 = '01';
    const VAT_REGIME_02 = '02';
    const VAT_REGIME_03 = '03';
    const VAT_REGIME_04 = '04';
    const VAT_REGIME_05 = '05';
    const VAT_REGIME_06 = '06';
    const VAT_REGIME_07 = '07';
    const VAT_REGIME_08 = '08';
    const VAT_REGIME_09 = '09';
    const VAT_REGIME_10 = '10';
    const VAT_REGIME_11 = '11';
    const VAT_REGIME_12 = '12';
    const VAT_REGIME_13 = '13';
    const VAT_REGIME_14 = '14';
    const VAT_REGIME_15 = '15';
    const VAT_REGIME_51 = '51';
    const VAT_REGIME_52 = '52';

    private string $description;
    private Ammount $total;
    private ?Ammount $supportedRetention = null;
    private ?Ammount $taxBaseCost = null;
    private array $vatRegime = [];
    private array $details = [];

    public function __construct(string $description, Ammount $total, array $vatRegimes, ?Ammount $supportedRetention = null, ?Ammount $taxBaseCost = null)
    {
        $this->description = $description;
        $this->total = $total;
        $this->setVatRegimes($vatRegimes);

        if ($supportedRetention) {
            $this->supportedRetention = $supportedRetention;
        }

        if ($taxBaseCost) {
            $this->taxBaseCost = $taxBaseCost;
        }
    }

    private function setVatRegimes(array $vatRegimes): self
    {
        if (!sizeof($vatRegimes)) {
            throw new InvalidArgumentException('Empty vat regimes is not allowed');
        }

        foreach ($vatRegimes as $vatRegime) {
            $this->addVatRegime($vatRegime);
        }

        return $this;
    }

    public function addVatRegime(string $vatRegime): self
    {
        if (!in_array($vatRegime, $this->validVatRegimes())) {
            throw new InvalidVatRegimeException();
        }

        if (!sizeof($this->vatRegime) < 2) {
            throw new OutOfBoundsException('Too many subject and not exempt breakdown items');
        }

        $this->vatRegime[] = $vatRegime;
        return $this;
    }

    private function validVatRegimes(): array
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
            self::VAT_REGIME_10,
            self::VAT_REGIME_11,
            self::VAT_REGIME_12,
            self::VAT_REGIME_13,
            self::VAT_REGIME_14,
            self::VAT_REGIME_15,
            self::VAT_REGIME_51,
            self::VAT_REGIME_52,
        ];
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = $domDocument->createElement('DatosFactura');
        $data->appendChild(
            $domDocument->createElement('DescripcionFactura', $this->description)
        );
        if (sizeof($this->details)) {
            $details = $domDocument->createElement('DetallesFactura');
            foreach ($this->details as $detail) {
                $details->appendChild($detail->xml($domDocument));
                $data->appendChild($details);
            }
        }

        $data->appendChild(
            $domDocument->createElement('ImporteTotalFactura', $this->total)
        );

        if ($this->supportedRetention) {
            $data->appendChild(
                $domDocument->createElement('RetencionSoportada', $this->supportedRetention)
            );
        }

        if ($this->taxBaseCost) {
            $data->appendChild(
                $domDocument->createElement('BaseImponibleACoste', $this->taxBaseCost)
            );
        }

        $vatRegimeKeys = $domDocument->createElement('Claves', $this->taxBaseCost);
        foreach ($this->vatRegime as $vatRegime) {
            $keyId = $domDocument->createElement('IDClave');
            $keyId->appendChild(
                $domDocument->createElement('ClaveRegimenIvaOpTrascendencia', $vatRegime)
            );

            $vatRegimeKeys->appendChild($keyId);
        }
        $data->appendChild($vatRegimeKeys);
        return $data;
    }

    public function total(): Ammount
    {
        return $this->total;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'description' => [
                    'type' => 'string',
                    'maxLength' => 250,
                ],
                'total' => [
                    'type' => 'string',
                    'pattern' => '(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa guztira (2 dezimalekin) - Importe total (2 decimales)'
                ],
                'vatRegimes' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'description'
                    ],
                    'minItem' => 1,
                    'maxItems' => 3,
                    'description' => 'Gakoak: BEZaren araubideen eta zerga-ondorioak dauzkaten eragiketak - Claves de regímenes de IVA y operaciones con trascendencia tributaria'
                ],
                'supportedRetention' => [
                    'type' => 'string',
                    'pattern' => '(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Jasandako atxikipena (2 dezimalekin) - Retención soportada (2 decimales)'
                ],
                'taxBaseCost' => [
                    'type' => 'string',
                    'pattern' => '(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Kosturako zerga-oinarria (2 dezimalekin) - Base imponible a coste (2 decimales)'
                ]
            ],
            'required' => ['description', 'total', 'vatRegimes']
        ];
    }
}
// <element name="FechaOperacion" type="T:FechaType" minOccurs="0"/>
