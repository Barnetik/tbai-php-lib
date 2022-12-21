<?php

namespace Barnetik\Tbai\Api\Bizkaia\IncomeTax;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class Collection implements TbaiXml
{
    private array $incomeTaxDetails = [];

    public function addDetail(Detail $detail): self
    {
        array_push($this->incomeTaxDetails, $detail);
        return $this;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $incomeTaxDetails = $domDocument->createElement('Renta');
        foreach ($this->incomeTaxDetails as $detail) {
            $incomeTaxDetails->appendChild($detail->xml($domDocument));
        }
        return $incomeTaxDetails;
    }

    public static function createFromJson(array $jsonData): self
    {
        $incomeTaxCollection = new self();
        $incomeTaxDetails = $jsonData['incomeTaxDetails'];
        foreach ($incomeTaxDetails as $incomeTaxDetail) {
            $incomeTaxCollection->addDetail(Detail::createFromJson($incomeTaxDetail));
        }

        return $incomeTaxCollection;
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'incomeTaxDetails' => [
                    'items' => Detail::docJson(),
                    'minItems' => 1,
                    'maxItems' => 10
                ],
            ],
            'required' => ['epigraph']
        ];
    }

    public function toArray(): array
    {
        return [
            'incomeTaxDetails' => array_map(function ($incomeTaxDetail) {
                return $incomeTaxDetail->toArray();
            }, $this->incomeTaxDetails),
        ];
    }
}
