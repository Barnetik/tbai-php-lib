<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Amount;
use Barnetik\Tbai\Exception\InvalidExemptionReasonException;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use DOMXPath;

class AbstractSubjectExemptBreakdownItem implements TbaiXml
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

    final public function __construct(Amount $taxBase, string $reason)
    {
        $this->taxBase = $taxBase;
        $this->setExemptionReason($reason);
    }

    private static function validExemptionReasons(): array
    {
        return [
            static::EXEMPT_REASON_E1,
            static::EXEMPT_REASON_E2,
            static::EXEMPT_REASON_E3,
            static::EXEMPT_REASON_E4,
            static::EXEMPT_REASON_E5,
            static::EXEMPT_REASON_E6
        ];
    }

    /**
     * @return static
     */
    private function setExemptionReason(string $reason)
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
        $exemptDetail->appendChild($domDocument->createElement('CausaExencion', $this->exemptionReason));
        $exemptDetail->appendChild($domDocument->createElement('BaseImponible', $this->taxBase));
        return $exemptDetail;
    }

    /**
     * @return static
     */
    public static function createFromXml(DOMXPath $xpath, DOMNode $contextNode)
    {
        $taxBase = new Amount($xpath->evaluate('string(BaseImponible)', $contextNode));
        $reason = $xpath->evaluate('string(CausaExencion)', $contextNode);

        return new static($taxBase, $reason);
    }

    /**
     * @return static
     */
    public static function createFromJson(array $jsonData)
    {
        $taxBase = new Amount($jsonData['taxBase']);
        $reason = $jsonData['reason'];
        return new static($taxBase, $reason);
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
