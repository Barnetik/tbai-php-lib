<?php

namespace Barnetik\Tbai\Interface;

use DOMDocument;
use DOMNode;

interface TbaiXml
{
    public function xml(DOMDocument $domDocument): DOMNode;
}
