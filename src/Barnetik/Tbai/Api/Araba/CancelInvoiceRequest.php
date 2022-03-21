<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Api\ApiRequestInterface;

class CancelInvoiceRequest implements ApiRequestInterface
{
    const URL = '/anulaciones';
    protected string $endpoint;
    protected TicketBaiCancel $ticketbaiCancel;

    public function __construct(TicketBaiCancel $ticketbaiCancel, string $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->ticketbaiCancel = $ticketbaiCancel;
    }

    public function url(): string
    {
        return $this->endpoint . static::URL;
    }

    public function data(): string
    {
        return $this->ticketbaiCancel->signed();
    }

    public function jsonDataHeader(): string
    {
        return json_encode([]);
    }
}
