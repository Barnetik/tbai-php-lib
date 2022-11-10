<?php

namespace Barnetik\Tbai\Invoice\Data;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;
use DOMXPath;

class Detail implements TbaiXml
{
    private string $description;
    private Amount $quantity;
    private Amount $unitPrice;
    private ?Amount $discount = null;
    private Amount $totalAmount;

    public function __construct(string $description, Amount $unitPrice, Amount $quantity, Amount $totalAmount, Amount $discount = null)
    {
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->totalAmount = $totalAmount;
        if ($discount) {
            $this->discount = $discount;
        }
    }

    public static function createFromJson(array $jsonData): self
    {
        $description = $jsonData['description'];
        $unitPrice = new Amount($jsonData['unitPrice'], 12, 8);
        $quantity = new Amount($jsonData['quantity']);
        $totalAmount = new Amount($jsonData['totalAmount']);

        $discount = null;
        if (isset($jsonData['discount']) && $jsonData['discount'] !== '') {
            $discount = new Amount($jsonData['discount']);
        }

        $detail = new Detail($description, $unitPrice, $quantity, $totalAmount, $discount);

        return $detail;
    }

    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode): self
    {
        $description = $xpath->evaluate('string(DescripcionDetalle)', $contextNode);
        $unitPrice = new Amount($xpath->evaluate('string(ImporteUnitario)', $contextNode), 12, 8);
        $quantity = new Amount($xpath->evaluate('string(Cantidad)', $contextNode));
        $totalAmount = new Amount($xpath->evaluate('string(ImporteTotal)', $contextNode));

        $discount = null;
        $discountValue = $xpath->evaluate('string(Descuento)', $contextNode);
        if ($discountValue) {
            $discount = new Amount($discountValue);
        }

        return new self($description, $unitPrice, $quantity, $totalAmount, $discount);
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $data = $domDocument->createElement('IDDetalleFactura');

        $data->appendChild(
            $domDocument->createElement('DescripcionDetalle', htmlspecialchars($this->description, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'))
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
            $domDocument->createElement('ImporteTotal', $this->totalAmount)
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
                    'description' => 'Zenbatekoa, aleko (BEZ gabe, 2 dezimalekin) - Importe unitario (sin IVA, 2 decimales)'
                ],
                'quantity' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Kopurua (2 dezimalekin) - Cantidad (2 decimales)'
                ],
                'discount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Deskontua (2 dezimalekin) - Descuento (Sin IVA, 2 decimales)'
                ],
                'totalAmount' => [
                    'type' => 'string',
                    'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
                    'description' => 'Zenbatekoa, guztira (BEZ barne, 2 dezimalekin) - Importe total (con IVA, 2 decimales)'
                ],
            ],
            'required' => ['description', 'unitPrice', 'quantity', 'totalAmount']
        ];
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'unitPrice' => (string)$this->unitPrice,
            'quantity' => (string)$this->quantity,
            'discount' => $this->discount ? (string)$this->discount : null,
            'totalAmount' => (string)$this->totalAmount,
        ];
    }
}
