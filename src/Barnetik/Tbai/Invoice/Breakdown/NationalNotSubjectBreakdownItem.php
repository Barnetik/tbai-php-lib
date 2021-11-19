<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\TypeChecker\Ammount;
use Barnetik\Tbai\Exception\InvalidAmmountException;
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

    private Ammount $ammountChecker;

    public function __construct(string $ammount, string $reason)
    {
        $this->ammountChecker = new Ammount();
        $this->setAmmount($ammount);
        $this->setNotSubjectReason($reason);
    }

    private function setAmmount(string $ammount): self
    {
        $this->ammountChecker->check($ammount);
        $this->ammount = $ammount;
        return $this;
    }

    private function validNotSubjectReasons(): array
    {
        return [
            self::NOT_SUBJECT_REASON_RL,
            self::NOT_SUBJECT_REASON_OT,
        ];
    }

    private function setNotSubjectReason(string $reason): self
    {
        if (!in_array($reason, $this->validNotSubjectReasons())) {
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
}
