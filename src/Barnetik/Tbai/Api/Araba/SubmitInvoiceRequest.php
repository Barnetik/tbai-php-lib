<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\Api\ApiRequestInterface;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const URL = '/facturas';
    protected string $endpoint = 'https://pruebas-ticketbai.araba.eus/TicketBAI/v1';
    protected TicketBai $ticketbai;

    public function __construct(TicketBai $ticketbai, string $endpoint = null)
    {
        if ($this->endpoint) {
            $this->endpoint = $endpoint;
        }

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
