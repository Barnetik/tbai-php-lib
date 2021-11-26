<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Subject\Emitter;
use Barnetik\Tbai\Subject\Recipient;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;

class Subject implements TbaiXml
{
    const EMITTED_BY_EMITTER = 'N';
    const EMITTED_BY_THIRD_PARTY = 'T';
    const EMITTED_BY_RECIPIENT = 'D';

    protected Emitter $emitter;
    protected array $recipients = [];
    protected string $emittedBy;

    public function __construct(Emitter $emitter, Recipient $recipient, string $emittedBy = self::EMITTED_BY_EMITTER)
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

    public function emitterVatId(): VatId
    {
        return $this->emitter->vatId();
    }

    public function emitterName(): string
    {
        return $this->emitter->name();
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

    public function xml(DOMDocument $document): DOMNode
    {
        $subject = $document->createElement('Sujetos');

        $recipients = $document->createElement('Destinatarios');
        foreach ($this->recipients as $recipient) {
            $recipients->appendChild(
                $recipient->xml($document)
            );
        }

        $subject->appendChild($this->emitter->xml($document));
        $subject->appendChild($recipients);
        // $subject->appendChild($recipients);
        $subject->appendChild($document->createElement('VariosDestinatarios', $this->multipleRecipients()));
        $subject->appendChild($document->createElement('EmitidaPorTercerosODestinatario', $this->emittedBy()));

        return $subject;
    }

    public function docJson(): array
    {
        return [
            'recipients',
            'emmiter',
            'multipleRecipients',
            'emitedBy',
        ];
    }
}
