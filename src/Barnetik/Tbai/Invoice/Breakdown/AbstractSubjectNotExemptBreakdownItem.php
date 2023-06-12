<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Exception\InvalidNotExemptTypeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;
use OutOfBoundsException;

class AbstractSubjectNotExemptBreakdownItem implements TbaiXml
{
    const NOT_EXEMPT_TYPE_S1 = 'S1';
    const NOT_EXEMPT_TYPE_S2 = 'S2';

    private string $notExemptType;
    private array $vatDetails = [];

    final public function __construct(string $type, array $vatDetails)
    {
        $this->setNotExemptType($type);
        if (!sizeof($vatDetails)) {
            throw new InvalidArgumentException('VatDetails cannot be empty');
        }
        foreach ($vatDetails as $vatDetail) {
            $this->addVatDetail($vatDetail);
        }
    }

    private static function validNotExemptTypes(): array
    {
        return [
            static::NOT_EXEMPT_TYPE_S1,
            static::NOT_EXEMPT_TYPE_S2,
        ];
    }

    /**
     * @return static
     */
    private function setNotExemptType(string $type)
    {
        if (!in_array($type, self::validNotExemptTypes())) {
            throw new InvalidNotExemptTypeException();
        }
        $this->notExemptType = $type;

        return $this;
    }

    /**
     * @return static
     */
    public function addVatDetail(VatDetail $vatDetail)
    {
        if (sizeof($this->vatDetails) < 6) {
            $this->vatDetails[] = $vatDetail;
            return $this;
        }

        throw new OutOfBoundsException('Too many vat detail items');
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $notExentType = $domDocument->createElement('DetalleNoExenta');
        $notExentType->appendChild($domDocument->createElement('TipoNoExenta', $this->notExemptType));
        $vatBreakdown = $domDocument->createElement('DesgloseIVA');

        foreach ($this->vatDetails as $vatDetail) {
            $vatBreakdown->appendChild($vatDetail->xml($domDocument));
        }

        $notExentType->appendChild($vatBreakdown);

        return $notExentType;
    }

    /**
     * @return static
     */
    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode)
    {
        $type = $xpath->evaluate('string(TipoNoExenta)', $contextNode);

        $vatDetails = [];
        foreach ($xpath->query('DesgloseIVA/DetalleIVA', $contextNode) as $node) {
            $vatDetails[] = VatDetail::createFromXml($xpath, $node);
        }

        return new static($type, $vatDetails);
    }

    /**
     * @return static
     */
    public static function createFromJson(array $jsonData)
    {
        $type = $jsonData['type'];
        $vatDetails = [];
        foreach ($jsonData['vatDetails'] as $vatDetailData) {
            $vatDetails[] = VatDetail::createFromJson($vatDetailData);
        }
        return new static($type, $vatDetails);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'vatDetails' => [
                    'type' => 'array',
                    'description' => 'Zenbatekoak - Importes',
                    'minItems' => 1,
                    'maxItems' => 6,
                    'items' => VatDetail::docJson()
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => self::validNotExemptTypes(),
                    'description' => '
Salbuetsi gabeko mota - Tipo de no exenta
  * S1: sub. pas. inbertsiorik ez - sin ISP
  * S2: sub. pas. inbertsioa - con ISP
'
                ],
            ],
            'required' => ['type', 'vatDetails']

        ];
    }

    public function toArray(): array
    {
        return [
            'type' => $this->notExemptType,
            'vatDetails' => array_map(function ($vatDetail) {
                return $vatDetail->toArray();
            }, $this->vatDetails),
        ];
    }
}
