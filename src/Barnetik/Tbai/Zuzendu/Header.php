<?php

namespace Barnetik\Tbai\Zuzendu;

use DOMDocument;
use DOMNode;

class Header
{
    public const ACTION_RECTIFY = 'SUBSANAR';
    public const ACTION_MODIFY = 'MODIFICAR';

    private const ZUZENDU_VERSION = '1.0';

    private ?string $action;

    public function __construct(?string $action = null)
    {
        $this->action = $action;
    }

    public static function createRectify(): self
    {
        return new self(self::ACTION_RECTIFY);
    }

    public static function createModify(): self
    {
        return new self(self::ACTION_MODIFY);
    }

    public function xml(DOMDocument $document): DOMNode
    {
        $header = $document->createElement('Cabecera');
        $version = $document->createElement('IDVersion', self::ZUZENDU_VERSION);
        $header->appendChild($version);

        if ($this->action !== null) {
            $action = $document->createElement('Accion', $this->action);
            $header->appendChild($action);
        }

        return $header;
    }

    public static function createFromJson(array $jsonData): self
    {
        return new self($jsonData['action']);
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action
        ];
    }
}
