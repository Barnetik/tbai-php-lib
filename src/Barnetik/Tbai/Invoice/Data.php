<?php

namespace Barnetik\Tbai\Invoice;

use Barnetik\Tbai\TypeChecker\Ammount;
use Barnetik\Tbai\Exception\InvalidVatRegimeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
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
    private string $total;
    private ?string $supportedRetention = null;
    private ?string $taxBaseCost = null;
    private array $vatRegime = [];
    private array $details = [];
    private Ammount $ammountChecker;

    public function __construct(string $description, string $total, array $vatRegimes, ?string $supportedRetention = null, ?string $taxBaseCost = null)
    {
        $this->ammountChecker = new Ammount();

        $this->description = $description;
        $this->setTotal($total);
        $this->setVatRegimes($vatRegimes);

        if ($supportedRetention) {
            $this->setSupportedRetention($supportedRetention);
        }

        if ($taxBaseCost) {
            $this->setTaxBaseCost($taxBaseCost);
        }
    }

    private function setTotal(string $total): self
    {
        $this->ammountChecker->check($total, 12);

        $this->total = $total;
        return $this;
    }

    private function setSupportedRetention(string $supportedRetention): self
    {
        $this->ammountChecker->check($supportedRetention, 12);

        $this->supportedRetention = $supportedRetention;
        return $this;
    }

    private function setTaxBaseCost(string $taxBaseCost): self
    {
        $this->ammountChecker->check($taxBaseCost, 12);

        $this->taxBaseCost = $taxBaseCost;
        return $this;
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
}

// <element name="FechaOperacion" type="T:FechaType" minOccurs="0"/>
