<?php

namespace Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\TicketBai;
use Barnetik\Tbai\Api\ApiRequestInterface;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const URL = '/sarrerak/alta';
    protected string $endpoint = 'https://tbai-z.egoitza.gipuzkoa.eus';
    protected TicketBai $ticketbai;

    public function __construct(TicketBai $ticketbai, string $endpoint)
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
