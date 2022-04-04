<?php

namespace Barnetik\Tbai\Header;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\Amount;
use DOMDocument;
use DOMNode;

class RectifyingAmount implements TbaiXml
{
    private Amount $base;
    private Amount $quota;
    private ?Amount $surcharge = null;

    public function __construct(Amount $base, Amount $quota, Amount $surcharge = null)
    {
        $this->base = $base;
        $this->quota = $quota;
        if ($surcharge) {
            $this->surcharge = $surcharge;
        }
    }

    public static function createFromJson(array $jsonData): self
    {
        $base = new Amount($jsonData['base']);
        $quota = new Amount($jsonData['quota']);

        $surcharge = null;
        if (isset($jsonData['surcharge']) && $jsonData['surcharge'] !== '') {
            $surcharge = new Amount($jsonData['surcharge']);
        }

        $detail = new RectifyingAmount($base, $quota, $surcharge);

        return $detail;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $rectifyingAmount = $domDocument->createElement('ImporteRectificacionSustitutiva');

        $rectifyingAmount->appendChild(
            $domDocument->createElement('BaseRectificada', $this->base)
        );

        $rectifyingAmount->appendChild(
            $domDocument->createElement('CuotaRectificada', $this->quota)
        );

        if ($this->surcharge) {
            $rectifyingAmount->appendChild(
                $domDocument->createElement('CuotaRecargoRectificada', $this->surcharge)
            );
        }

        return $rectifyingAmount;
    }

    public static function docJson(): array
    {
        return [
        'type' => 'object',
        'properties' => [
        'base' => [
          'type' => 'string',
          'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
          'description' => 'Ordezkatutako fakturaren zerga oinarria (2 dezimalekin) - Base imponible de la factura sustituida (2 decimales)'
        ],
        'quota' => [
          'type' => 'string',
          'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
          'description' => 'Ordezkatutako fakturaren jasanarazitako kuota (2 dezimalekin) - Cuota repercutida de la factura sustituida (2 decimales)'
        ],
        'surcharge' => [
          'type' => 'string',
          'pattern' => '^(\+|-)?\d{1,12}(\.\d{0,2})?$',
          'description' => 'Ordezkatutako fakturaren baliokidetasun errekarguaren kuota (2 dezimalekin) - Cuota del recargo de equivalencia de la factura sustituida. (2 decimales)'
        ],
        ],
        'required' => ['base', 'quota']
        ];
    }

    public function toArray(): array
    {
        return [
        'base' => (string)$this->base,
        'quota' => (string)$this->quota,
        'surcharge' => $this->surcharge ? (string)$this->surcharge : null,
        ];
    }
}
