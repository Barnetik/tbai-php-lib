<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Exception\InvalidNotExemptTypeException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use OutOfBoundsException;

class NationalSubjectNotExemptBreakdownItem implements TbaiXml
{
    const NOT_EXEMPT_TYPE_S1 = 'S1';
    const NOT_EXEMPT_TYPE_S2 = 'S2';

    private string $notExemptType;
    private array $vatDetails = [];

    public function __construct(string $type, array $vatDetails)
    {
        $this->setNotExemptType($type);
        $this->vatDetails = $vatDetails;
    }

    private static function validNotExemptTypes(): array
    {
        return [
            self::NOT_EXEMPT_TYPE_S1,
            self::NOT_EXEMPT_TYPE_S2,
        ];
    }

    private function setNotExemptType(string $type): self
    {
        if (!in_array($type, self::validNotExemptTypes())) {
            throw new InvalidNotExemptTypeException();
        }
        $this->notExemptType = $type;

        return $this;
    }

    public function addVatDetail(VatDetail $vatDetail): self
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

        foreach ($this->vatDetails as $vatDetail) {
            $notExentType->appendChild($domDocument->createElement('TipoNoExenta', $this->notExemptType));
            $vatBreakdown = $domDocument->createElement('DesgloseIVA');
            $vatBreakdown->appendChild($vatDetail->xml($domDocument));
            $notExentType->appendChild($vatBreakdown);
        }

        return $notExentType;
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
}
