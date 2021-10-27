<?php

namespace Barnetik\Tbai;

class InvoiceSubject
{
    protected $emisor;
    protected $receptors = [];

    public function __construct($emisor, Receptor $receptor)
    {
        $this->emisor;
        $this->addReceptor($receptor);
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
}
