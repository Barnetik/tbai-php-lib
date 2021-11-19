<?php

namespace Barnetik\Tbai\Subject;

use Barnetik\Tbai\Interfaces\TbaiXml;
use Barnetik\Tbai\TypeChecker\VatId;
use DOMDocument;
use DOMNode;

class Emitter implements TbaiXml
{
    protected string $vatId;
    protected string $name;
    protected VatId $vatIdChecker;


    public function __construct(string $vatId, string $name)
    {
        $this->vatIdChecker = new VatId();
        $this->setVatId($vatId);
        $this->name = $name;
    }

    public function setVatId(string $vatId): self
    {
        $this->vatIdChecker->check($vatId);
        $this->vatId = $vatId;

        return $this;
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
