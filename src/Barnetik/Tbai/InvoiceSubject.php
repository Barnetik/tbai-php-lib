<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;

class InvoiceSubject
{
    const EMITTED_BY_EMITTER = 'N';
    const EMITTED_BY_THIRD_PARTY = 'T';
    const EMITTED_BY_RECIPIENT = 'D';

    protected Emitter $emitter;
    protected array $recipients = [];
    protected string $emittedBy;

    public function __construct(Emitter $emitter, Recipient $recipient, string $emittedBy)
    {
        $this->emitter = $emitter;
        $this->addRecipient($recipient);
        $this->emittedBy = $emittedBy;
    }

    public function addRecipient(Recipient $recipient): self
    {
        array_push($this->recipients, $recipient);
        return $this;
    }

    public function emitter(): Emitter
    {
        return $this->emitter;
    }

    public function recipients(): array
    {
        return $this->recipients;
    }

    public function multipleRecipients(): string
    {
        if ($this->hasMultipleRecipients()) {
            return 'S';
        }
        return 'N';
    }

    public function hasMultipleRecipients(): bool
    {
        return sizeof($this->recipients) > 1;
    }

    public function emittedBy(): string
    {
        return $this->emittedBy;
    }
}
