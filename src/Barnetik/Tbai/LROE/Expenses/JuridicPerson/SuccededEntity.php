<?php

namespace Barnetik\Tbai\LROE\Expenses\JuridicPerson;

use Barnetik\Tbai\Interfaces\TbaiXml;
use DOMDocument;
use DOMNode;

class SuccededEntity implements TbaiXml
{
    protected string $name;
    protected string $vatId;

    private function __construct(string $name, string $vatId)
    {
        $this->name = $name;
        $this->vatId = $vatId;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $entity = $domDocument->createElement('EntidadSucedida');
        $entity->appendChild($domDocument->createElement('NombreRazon', $this->name));
        $entity->appendChild($domDocument->createElement('NIF', $this->vatId));
        return $entity;
    }

    public static function createFromJson(array $jsonData): self
    {
        return new self($jsonData['name'], $jsonData['vatId']);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'vatId' => $this->vatId,
        ];
    }

    public static function docJson(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'maxLength' => 120,
                    'description' => 'Nombre o razón social de la entidad sucedida'
                ],
                'vatId' => [
                    'type' => 'string',
                    'minLength' => 9,
                    'maxLength' => 9,
                    'pattern' => '(([a-z|A-Z]{1}\d{7}[a-z|A-Z]{1})|(\d{8}[a-z|A-Z]{1})|([a-z|A-Z]{1}\d{8}))',
                    'description' => 'NIF de la entidad sucedida'
                ],
            ],
            'required' => ['name', 'vatId']
        ];
    }
}
