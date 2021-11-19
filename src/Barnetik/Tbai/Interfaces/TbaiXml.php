<?php

namespace Barnetik\Tbai\Interfaces;

use DOMDocument;
use DOMNode;

interface TbaiXml
{
    public function xml(DOMDocument $domDocument): DOMNode;
}
