<?php

namespace Barnetik\Tbai;

class InvoiceSubject
{
    protected $emisor;
    protected $receptor;


    public function emisor()
    {
        return $this->emisor;
    }

    public function receptor()
    {
        return $this->receptor;
    }
}
