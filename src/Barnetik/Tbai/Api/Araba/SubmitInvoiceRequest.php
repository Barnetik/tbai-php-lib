<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\SubmitInvoiceFile;
use Barnetik\Tbai\Api\ApiRequestInterface;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const URL = '/facturas';
    protected string $endpoint;
    protected SubmitInvoiceFile $ticketbai;

    public function __construct(SubmitInvoiceFile $ticketbai, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->ticketbai = $ticketbai;
    }

    public function url(): string
    {
        return $this->endpoint . static::URL;
    }

    public function data(): string
    {
        return $this->ticketbai->signed();
    }

    public function jsonDataHeader(): string
    {
        return json_encode([]);
    }
}
