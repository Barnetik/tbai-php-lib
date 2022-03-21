<?php

namespace Barnetik\Tbai\Api\Araba;

use Barnetik\Tbai\Api\ApiRequestInterface;
use Barnetik\Tbai\Api\AbstractTerritory;
use Barnetik\Tbai\SubmitInvoiceFile;

class Endpoint extends AbstractTerritory
{
    const SUBMIT_ENDPOINT_DEV = 'https://pruebas-ticketbai.araba.eus/TicketBAI/v1';
    const SUBMIT_ENDPOINT = 'https://ticketbai.araba.eus/TicketBAI/v1';

    public function headers(ApiRequestInterface $apiRequest, string $dataFile): array
    {
        return [
            'Content-Type: application/xml;charset=UTF-8'
        ];
    }

    public function createSubmitInvoiceRequest(SubmitInvoiceFile $ticketBai): ApiRequestInterface
    {
        return new SubmitInvoiceRequest($ticketBai, $this->getSubmitEndpoint());
    }

    protected function response(string $status, array $headers, string $content): Response
    {
        return new Response($status, $headers, $content);
    }
}
