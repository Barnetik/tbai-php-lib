<?php

namespace Barnetik\Tbai;

class InvoiceSubject
{
    const EMITED_BY_EMISOR = 'N';
    const EMITED_BY_THIRD_PARTY = 'T';
    const EMITED_BY_RECEPTOR = 'D';

    protected $emisor;
    protected $receptors = [];
    protected $emitedBy;

    public function __construct(Emisor $emisor, Receptor $receptor, $emitedBy)
    {
        $this->emisor = $emisor;
        $this->addReceptor($receptor);
        $this->emitedBy = $emitedBy;
    }

    public function addReceptor(Receptor $receptor)
    {
        array_push($this->receptors, $receptor);
    }

    public function emisor()
    {
        return $this->emisor;
    }

    public function receptors()
    {
        return $this->receptors;
    }

    public function multipleReceptors()
    {
        return sizeof($this->receptors) > 1;
    }

    public function emitedBy()
    {
        return $this->emitedBy;
    }
}
