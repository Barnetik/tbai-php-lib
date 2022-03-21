<?php

namespace Barnetik\Tbai\Api\Bizkaia;

use Barnetik\Tbai\TicketBaiCancel;
use Barnetik\Tbai\Api\ApiRequestInterface;
use Exception;

class CancelInvoiceRequest implements ApiRequestInterface
{
    const URL = '/anulaciones';
    protected string $endpoint;
    protected TicketBaiCancel $ticketbaiCancel;

    public function __construct(TicketBaiCancel $ticketbaiCancel, string $endpoint)
    {
        throw new Exception('Uninmplemented endpoint');
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
