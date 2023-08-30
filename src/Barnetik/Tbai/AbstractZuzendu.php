<?php

namespace Barnetik\Tbai;

use DOMNode;
use Barnetik\Tbai\Interfaces\Stringable;
use DOMDocument;
use JsonSerializable;
use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\Exception\InvalidTerritoryException;

abstract class AbstractZuzendu implements TbaiXml, Stringable, JsonSerializable
{
    const TERRITORY_ARABA = '01';
    const TERRITORY_BIZKAIA = '02';
    const TERRITORY_GIPUZKOA = '03';

    protected string $territory;

    public function __construct(string $territory)
    {
        if (!in_array($territory, self::validTerritories())) {
            throw new InvalidTerritoryException();
        }
        $this->territory = $territory;
    }

    abstract public function xml(DOMDocument $document): DOMNode;
    abstract public function toArray(): array;

    protected static function validTerritories(): array
    {
        return [
            self::TERRITORY_ARABA,
            self::TERRITORY_BIZKAIA,
            self::TERRITORY_GIPUZKOA,
        ];
    }

    public function territory(): string
    {
        return $this->territory;
    }

    public function dom(): DomDocument
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $domNode = $this->xml($xml);
        $xml->appendChild($domNode);
        return $xml;
    }

    public function __toString(): string
    {
        return $this->dom()->saveXml();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
