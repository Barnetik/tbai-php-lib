<?php

namespace Barnetik\Tbai;

use Barnetik\Tbai\Invoice\Breakdown;
use Barnetik\Tbai\Invoice\Data;
use Barnetik\Tbai\Invoice\Header;

class Invoice
{
    private Header $header;
    private Data $data;
    private Breakdown $breakdown;

    public function __construct(Header $header, Data $data, Breakdown $breakdown)
    {
        $this->header = $header;
        $this->data = $data;
        $this->breakdown = $breakdown;
    }
}
