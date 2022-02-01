<?php

namespace Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\TicketBai;
use DOMDocument;
use DOMElement;
use DOMNode;

class SubmitInvoiceRequest implements ApiRequestInterface
{
    const MODEL = '240';
    const URL = '/sarrerak/alta';

    private string $endpoint = 'https://tbai-z.egoitza.gipuzkoa.eus';
    private TicketBai $ticketbai;

    public function __construct(TicketBai $ticketbai, string $endpoint = null)
    {
        if ($this->endpoint) {
            $this->endpoint = $endpoint;
        }

        $this->ticketbai = $ticketbai;
    }

    public function url(): string
    {
        return $this->endpoint . self::URL;
    }

    public function data(): string
    {
        return (string)$this->ticketbai;
    }

    public function jsonDataHeader(): string
    {
        return json_encode([]);
    }
}
