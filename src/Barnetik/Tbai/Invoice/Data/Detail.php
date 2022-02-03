<?php

namespace Barnetik\Tbai\Invoice\Data;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Ammount;
use DOMDocument;
use DOMNode;

class Detail implements TbaiXml
{
    private string $description;
    private Ammount $quantity;
    private Ammount $unitPrice;
    private ?Ammount $discount = null;
    private Ammount $totalAmmount;

    public function __construct(string $description, Ammount $unitPrice, Ammount $quantity, Ammount $totalAmmount, Ammount $discount = null)
    {
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->totalAmmount = $totalAmmount;
        if ($discount) {
            $this->discount = $discount;
        }
    }

    public static function createFromJson(array $jsonData): self
    {
        $description = $jsonData['description'];
        $unitPrice = new Ammount($jsonData['unitPrice']);
        $quantity = new Ammount($jsonData['quantity']);
        $totalAmmount = new Ammount($jsonData['totalAmmount']);

        $discount = null;
        if (isset($jsonData['discount'])) {
            $discount = new Ammount($jsonData['discount']);
        }

        $detail = new Detail($description, $unitPrice, $quantity, $totalAmmount, $discount);

        return $detail;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = $domDocument->createElement('IDDetalleFactura');

        $data->appendChild(
            $domDocument->createElement('DescripcionDetalle', $this->description)
        );

        $data->appendChild(
            $domDocument->createElement('Cantidad', $this->quantity)
        );

        $data->appendChild(
            $domDocument->createElement('ImporteUnitario', $this->unitPrice)
        );

        if ($this->discount) {
            $data->appendChild(
                $domDocument->createElement('Descuento', $this->discount)
            );
        }

        $data->appendChild(
            $domDocument->createElement('ImporteTotal', $this->totalAmmount)
        );

        return $data;
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
                'unitPrice' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa, aleko (2 dezimalekin) - Importe unitario (2 decimales)'
                ],
                'quantity' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Kopurua (2 dezimalekin) - Cantidad (2 decimales)'
                ],
                'discount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Deskontua (2 dezimalekin) - Descuento (2 decimales)'
                ],
                'totalAmmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa, guztira (2 dezimalekin) - Importe total (2 decimales)'
                ],
            ],
            'required' => ['description', 'unitPrice', 'quantity', 'totalAmmount']
        ];
    }
}
