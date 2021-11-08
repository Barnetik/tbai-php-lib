<?php

namespace Barnetik\Tbai\Invoice\Breakdown;

use Barnetik\Tbai\Exception\InvalidAmmountException;

class NationalNotSubjectBreakdownItem
{
    const NOT_SUBJECT_REASON_RL = 'RL';
    const NOT_SUBJECT_REASON_LOCATION_RULES = 'RL';
    const NOT_SUBJECT_REASON_OTHER = 'OT';
    const NOT_SUBJECT_REASON_OT = 'OT';

    private bool $subject;
    private bool $exempt;
    private string $notSubjectReason;
    private string $ammount;

    public function __construct(string $ammount, string $reason)
    {
        $this->subject = false;
        $this->exempt = true;
        $this->setNotSubjectReason($reason);
        $this->ammount = $ammount;
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
            throw new InvalidAmmountException();
        }
        $this->notSubjectReason = $reason;

        return $this;
    }

    public function ammount()
    {
        return $this->ammount;
    }
}
