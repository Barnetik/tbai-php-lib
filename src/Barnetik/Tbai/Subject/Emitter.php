<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\ValueObject\VatId;
use DOMDocument;
use DOMNode;

class Emitter implements TbaiXml
{
    protected string $vatId;
    protected string $name;


    public function __construct(VatId $vatId, string $name)
    {
        $this->vatId = $vatId;
        $this->name = $name;
    }

    public function vatId(): string
    {
        return $this->vatId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function xml(DOMDocument $domDocument): DOMNode
    {
        $emitter = $domDocument->createElement('Emisor');
        $emitter->append(
            $domDocument->createElement('NIF', $this->vatId),
            $domDocument->createElement('ApellidosNombreRazonSocial', $this->name),
        );
        return $emitter;
    }
}
