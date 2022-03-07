<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\Exception\InvalidExemptionReasonException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class NationalSubjectExemptBreakdownItem implements TbaiXml
{
    const EXEMPT_REASON_E1 = 'E1';
    const EXEMPT_REASON_E2 = 'E2';
    const EXEMPT_REASON_E3 = 'E3';
    const EXEMPT_REASON_E4 = 'E4';
    const EXEMPT_REASON_E5 = 'E5';
    const EXEMPT_REASON_E6 = 'E6';
    const EXEMPT_REASON_ART_20 = 'E1';
    const EXEMPT_REASON_ART_21 = 'E2';
    const EXEMPT_REASON_ART_22 = 'E3';
    const EXEMPT_REASON_ART_23 = 'E4';
    const EXEMPT_REASON_ART_24 = 'E4';
    const EXEMPT_REASON_ART_25 = 'E5';
    const EXEMPT_REASON_OTHER = 'E6';

    private string $exemptionReason;
    private Amount $taxBase;

    public function __construct(Amount $taxBase, string $reason)
    {
        $this->taxBase = $taxBase;
        $this->setExemptionReason($reason);
    }

    private static function validExemptionReasons(): array
    {
        return [
            self::EXEMPT_REASON_E1,
            self::EXEMPT_REASON_E2,
            self::EXEMPT_REASON_E3,
            self::EXEMPT_REASON_E4,
            self::EXEMPT_REASON_E5,
            self::EXEMPT_REASON_E6
        ];
    }

    private function setExemptionReason(string $reason): self
    {
        if (!in_array($reason, self::validExemptionReasons())) {
            throw new InvalidExemptionReasonException();
        }
        $this->exemptionReason = $reason;

        return $this;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $exemptDetail = $domDocument->createElement('DetalleExenta');
        $exemptDetail->append(
            $domDocument->createElement('CausaExencion', $this->exemptionReason),
            $domDocument->createElement('BaseImponible', $this->taxBase),
        );
        return $exemptDetail;
    }

    public static function createFromJson(array $jsonData): self
    {
        $taxBase = new Amount($jsonData['taxBase']);
        $reason = $jsonData['reason'];
        return new self($taxBase, $reason);
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'taxBase' => [
                    'type' => 'string',
                    'description' => 'Salbuetsitako zerga-oinarria (2 dezimalekin) - Base imponible exenta (2 decimales)'
                ],
                'reason' => [
                    'type' => 'string',
                    'enum' => self::validExemptionReasons(),
                    'description' => '
Arrazoia - Razón:
  * E1: Salbuetsita 20. art. - Exenta Art.20
  * E2: Salbuetsita 21. art. - Exenta Art.21
  * E3: Salbuetsita 22. art. - Exenta Art.22
  * E4: Salbuetsita 23. art. eta 24. art. - Exenta Art.23 y 24
  * E5: Salbuetsita 25. art. - Exenta Art.25
  * E6: Salbuetsita Beste batzuk - Exenta Otros

'
                ],
            ],
            'required' => ['taxBase', 'reason']
        ];
    }

    public function toArray(): array
    {
        return [
            'taxBase' => (string)$this->taxBase,
            'reason' => $this->exemptionReason,
        ];
    }
}
