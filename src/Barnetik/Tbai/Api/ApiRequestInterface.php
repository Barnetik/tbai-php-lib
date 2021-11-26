<?php

namespace Barnetik\Tbai\Api;

interface ApiRequestInterface
{
    public function jsonDataHeader(): string;
    public function data(): string;
}
