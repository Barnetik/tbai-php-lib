<?php

namespace Barnetik\Tbai\Api\Gipuzkoa;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\Api\Bizkaia\SubmitInvoiceRequest;
use Barnetik\Tbai\TicketBai;

class Endpoint extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://tbai-z.egoitza.gipuzkoa.eus/sarrerak/alta';
    const SUBMIT_ENDPOINT = 'https://tbai-z.egoitza.gipuzkoa.eus/sarrerak/alta';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Content-Type: application/xml;charset=UTF-8'
        ];
    }


    public function createSubmitInvoiceRequest(TicketBai $ticketBai): ApiRequestInterface
    {
        return new SubmitInvoiceRequest($ticketBai);
    }
}
