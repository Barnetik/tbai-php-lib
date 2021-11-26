<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\ValueObject\Ammount;
use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;
use InvalidArgumentException;

class NationalNotSubjectBreakdownItem implements TbaiXml
{
    const NOT_SUBJECT_REASON_RL = 'RL';
    const NOT_SUBJECT_REASON_LOCATION_RULES = 'RL';
    const NOT_SUBJECT_REASON_OTHER = 'OT';
    const NOT_SUBJECT_REASON_OT = 'OT';

    private string $notSubjectReason;
    private string $ammount;

    public function __construct(Ammount $ammount, string $reason)
    {
        $this->ammount = $ammount;
        $this->setNotSubjectReason($reason);
    }

    private static function validNotSubjectReasons(): array
    {
        return [
            self::NOT_SUBJECT_REASON_RL,
            self::NOT_SUBJECT_REASON_OT,
        ];
    }

    private function setNotSubjectReason(string $reason): self
    {
        if (!in_array($reason, self::validNotSubjectReasons())) {
            throw new InvalidArgumentException('Subject reason is not valid');
        }
        $this->notSubjectReason = $reason;

        return $this;
    }

    public function ammount(): string
    {
        return $this->ammount;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $notSubjectDetail = $domDocument->createElement('DetalleNoSujeta');
        $notSubjectDetail->append(
            $domDocument->createElement('Causa', $this->notSubjectReason),
            $domDocument->createElement('Importe', $this->ammount),
        );
        return $notSubjectDetail;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'ammount' => [
                    'type' => 'string',
                    'description' => 'Zenbatekoa (2 dezimalekin) - Importe (2 decimales)'
                ],
                'notSubjectReason' => [
                    'type' => 'string',
                    'enum' => self::validNotSubjectReasons(),
                    'description' => 'Kargapean ez egoteko arrazoia - Causa no sujeción'
                ],
            ]
        ];
    }
}
