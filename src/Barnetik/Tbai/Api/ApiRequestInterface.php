<?php

namespace Barnetik\Tbai\Api;

use Barnetik\Tbai\SubmitInvoiceFile;

interface ApiRequestInterface
{
    public function __construct(SubmitInvoiceFile $ticketbai, string $endpoint);
    public function jsonDataHeader(): string;
    public function data(): string;
    public function url(): string;
}
