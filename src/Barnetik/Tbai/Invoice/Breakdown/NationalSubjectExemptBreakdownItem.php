<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Ammount;
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
    private Ammount $taxBase;

    public function __construct(Ammount $taxBase, string $reason)
    {
        $this->taxBase = $taxBase;
        $this->setExemptionReason($reason);
    }

    private function validExemptionReasons(): array
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
        if (!in_array($reason, $this->validExemptionReasons())) {
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
}
